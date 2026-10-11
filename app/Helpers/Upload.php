<?php
/**
 * File Upload Helper
 * Validates and moves uploaded files securely.
 */

declare(strict_types=1);

namespace App\Helpers;

class Upload
{
    /** Allowed MIME types per category */
    private static array $allowedTypes = [
        'image'    => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'video'    => ['video/mp4', 'video/webm', 'video/ogg'],
        'document' => ['application/pdf', 'image/jpeg', 'image/png'],
    ];

    /** Max sizes in bytes */
    private static array $maxSizes = [
        'image'    => 5 * 1024 * 1024,   // 5 MB
        'video'    => 100 * 1024 * 1024,  // 100 MB
        'document' => 10 * 1024 * 1024,   // 10 MB
    ];

    /**
     * Handle a single file upload.
     * Returns ['success' => bool, 'path' => string, 'filename' => string, 'error' => string]
     */
    public static function handle(array $file, string $category = 'image', string $subDir = ''): array
    {
        // Basic upload error check
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => self::uploadErrorMessage($file['error'])];
        }

        // Size check
        $maxSize = self::$maxSizes[$category] ?? (5 * 1024 * 1024);
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File size exceeds maximum allowed size of ' . Str::fileSize($maxSize)];
        }

        // MIME type check — use finfo, not the client-supplied type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = self::$allowedTypes[$category] ?? self::$allowedTypes['image'];
        if (!in_array($mime, $allowed, true)) {
            return ['success' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowed)];
        }

        // Extension validation (double extension attack prevention)
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeExts = self::mimeToExtensions($mime);
        if (!in_array($ext, $safeExts, true)) {
            return ['success' => false, 'error' => 'File extension does not match file type.'];
        }

        // Generate safe filename
        $newFilename = bin2hex(random_bytes(16)) . '.' . $ext;

        // Determine destination — public/uploads/ is web-accessible on both
        // XAMPP (htdocs/rental_website/public/uploads/) and cPanel (public_html/public/uploads/)
        $destDir = ROOT_PATH . '/public/uploads/' . trim($subDir, '/');
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $destPath = $destDir . '/' . $newFilename;
        $relativePath = 'uploads/' . trim($subDir, '/') . '/' . $newFilename;

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'error' => 'Failed to save uploaded file.'];
        }

        // Create thumbnail for images
        $thumbnail = null;
        if ($category === 'image') {
            $thumbnail = self::createThumbnail($destPath, $destDir, $newFilename);
        }

        return [
            'success'   => true,
            'filename'  => $newFilename,
            'path'      => $relativePath,
            'thumbnail' => $thumbnail,
            'size'      => $file['size'],
            'mime'      => $mime,
        ];
    }

    /**
     * Download a remote image (by URL) into local storage.
     * Returns the same shape as handle(). Hardened against SSRF, oversized
     * payloads, redirect-to-intranet tricks and non-image content.
     */
    public static function fetchFromUrl(string $url, string $subDir = 'properties/images'): array
    {
        $url = trim($url);

        // 1. Scheme + host validation — no ftp/data/file tricks
        $parts = parse_url($url);
        if (!$parts || empty($parts['host']) ||
            !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            return ['success' => false, 'error' => 'Not a valid http(s) URL.'];
        }

        // 2. SSRF guard — the host must resolve to a public IP
        if (!self::isPublicHost($parts['host'])) {
            return ['success' => false, 'error' => 'That host is not allowed.'];
        }

        if (!function_exists('curl_init')) {
            return ['success' => false, 'error' => 'cURL is not available on this server.'];
        }

        // 3. Stream to a temp file with a hard size cap (works even when the
        //    server lies about Content-Length)
        $maxSize = self::$maxSizes['image'];
        $tmp     = tempnam(sys_get_temp_dir(), 'imgfetch');
        $fp      = fopen($tmp, 'wb');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE            => $fp,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 3,
            CURLOPT_TIMEOUT         => 25,
            CURLOPT_CONNECTTIMEOUT  => 8,
            CURLOPT_USERAGENT       => 'Mozilla/5.0 (compatible; RentalWebsite-Importer/1.0)',
            CURLOPT_SSL_VERIFYPEER  => true,
            CURLOPT_SSL_VERIFYHOST  => 2,
            CURLOPT_NOPROGRESS      => false,
            CURLOPT_XFERINFOFUNCTION => function (int $dlNow) use ($maxSize): int {
                return $dlNow > $maxSize ? 1 : 0;   // non-zero aborts the transfer
            },
        ]);

        $ok         = curl_exec($ch);
        $status     = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $effective  = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        fclose($fp);

        // 4. A redirect must not have landed us on an internal host
        $finalHost = parse_url($effective, PHP_URL_HOST) ?: '';
        if ($finalHost !== '' && !self::isPublicHost($finalHost)) {
            self::cleanupTemp($tmp);
            return ['success' => false, 'error' => 'That host is not allowed.'];
        }

        $size = (int) (@filesize($tmp) ?: 0);
        if (!$ok || $status !== 200 || $size === 0) {
            self::cleanupTemp($tmp);
            return ['success' => false, 'error' => "Download failed (HTTP {$status})."]; 
        }
        if ($size > $maxSize) {
            self::cleanupTemp($tmp);
            return ['success' => false, 'error' => 'Remote image exceeds ' . Str::fileSize($maxSize) . '.'];
        }

        // 5. Verify the actual bytes are an image (never trust the URL/headers)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($tmp);
        $allowed = self::$allowedTypes['image'];
        if (!in_array($mime, $allowed, true)) {
            self::cleanupTemp($tmp);
            return ['success' => false, 'error' => 'The URL does not point to a JPEG, PNG, WebP or GIF image.'];
        }

        $safeExts   = self::mimeToExtensions($mime);
        $ext        = $safeExts[0] ?? 'jpg';
        $newFilename = bin2hex(random_bytes(16)) . '.' . $ext;

        // 6. Store alongside regular uploads (same dir / naming as handle())
        $destDir = ROOT_PATH . '/public/uploads/' . trim($subDir, '/');
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $destPath = $destDir . '/' . $newFilename;
        if (!rename($tmp, $destPath)) {
            self::cleanupTemp($tmp);
            return ['success' => false, 'error' => 'Could not save the downloaded image.'];
        }

        $thumbnail = self::createThumbnail($destPath, $destDir, $newFilename);

        return [
            'success'   => true,
            'filename'  => $newFilename,
            'path'      => 'uploads/' . trim($subDir, '/') . '/' . $newFilename,
            'thumbnail' => $thumbnail,
            'size'      => $size,
            'mime'      => $mime,
        ];
    }

    /** Host must resolve to a public, non-reserved IPv4/IPv6 */
    private static function isPublicHost(string $host): bool
    {
        $ip = gethostbyname(trim($host, '[]'));
        if ($ip === $host) return false;                 // DNS failure
        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    private static function cleanupTemp(string $tmp): void
    {
        if (is_file($tmp)) @unlink($tmp);
    }

    /** Create a thumbnail for an image */
    public static function createThumbnail(string $sourcePath, string $destDir, string $filename, int $thumbWidth = 400, int $thumbHeight = 300): ?string
    {
        if (!extension_loaded('gd')) return null;

        [$origW, $origH, $type] = getimagesize($sourcePath);
        if (!$origW) return null;

        $src = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG  => imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            IMAGETYPE_GIF  => imagecreatefromgif($sourcePath),
            default        => null,
        };
        if (!$src) return null;

        // Calculate proportional size
        $ratio = min($thumbWidth / $origW, $thumbHeight / $origH);
        $newW = (int) round($origW * $ratio);
        $newH = (int) round($origH * $ratio);

        $thumb = imagecreatetruecolor($newW, $newH);
        // Preserve transparency for PNG/GIF
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }
        imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $thumbFilename = 'thumb_' . $filename;
        $thumbPath = $destDir . '/' . $thumbFilename;

        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($thumb, $thumbPath, 85),
            IMAGETYPE_PNG  => imagepng($thumb, $thumbPath, 7),
            IMAGETYPE_WEBP => imagewebp($thumb, $thumbPath, 85),
            IMAGETYPE_GIF  => imagegif($thumb, $thumbPath),
            default        => null,
        };

        imagedestroy($src);
        imagedestroy($thumb);

        $relative = ltrim(str_replace(ROOT_PATH . '/public', '', $destDir . '/' . $thumbFilename), '/');
        return $relative;
    }

    /** Delete an uploaded file and its thumbnail */
    public static function delete(string $relativePath): void
    {
        // Paths are stored as 'uploads/...' relative to public/
        $full = ROOT_PATH . '/public/' . ltrim($relativePath, '/');
        if (file_exists($full)) unlink($full);

        // Also delete thumbnail if it exists
        $dir   = dirname($full);
        $file  = basename($full);
        $thumb = $dir . '/thumb_' . $file;
        if (file_exists($thumb)) unlink($thumb);
    }

    /** Map MIME type to valid extensions */
    private static function mimeToExtensions(string $mime): array
    {
        return match ($mime) {
            'image/jpeg'    => ['jpg', 'jpeg'],
            'image/png'     => ['png'],
            'image/webp'    => ['webp'],
            'image/gif'     => ['gif'],
            'video/mp4'     => ['mp4'],
            'video/webm'    => ['webm'],
            'video/ogg'     => ['ogv', 'ogg'],
            'application/pdf' => ['pdf'],
            default         => [],
        };
    }

    /** Human-readable PHP upload error */
    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the maximum allowed size.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the file upload.',
            default               => 'Unknown upload error.',
        };
    }
}
