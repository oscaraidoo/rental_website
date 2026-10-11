<?php
declare(strict_types=1);

/**
 * One-off seeder: assign the 9 seed photos to EVERY property.
 *
 * - Copies each photo to public/uploads/properties/images/{p{ID}_img{n}.jpg|webp}
 *   (same naming convention as existing seeded files).
 * - Generates GD thumbnails (thumb_ prefix) when GD is available.
 * - Inserts property_images rows; first photo per property is the cover
 *   (is_primary = 1) unless the property already has a cover.
 * - Idempotent: skips a property if it already received the seed set
 *   (detected via caption 'Seed photo N').
 *
 * Usage: php _assign_photos.php [host] [user] [pass] [database]
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$host = $argv[1] ?? 'localhost';
$user = $argv[2] ?? 'root';
$pass = $argv[3] ?? '';
$db   = $argv[4] ?? 'rental_website';

$ROOT      = __DIR__;
$seedDir   = $ROOT . '/database/seed_photos';
$uploadDir = $ROOT . '/public/uploads/properties/images';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }

const CAPTION_PREFIX = 'Seed photo ';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    fwrite(STDERR, "[FATAL] DB connect failed: {$e->getMessage()}\n");
    exit(1);
}

// Collect seed files (jpg/jpeg/jfif + webp), ordered for a nice cover first
$files = [];
foreach (scandir($seedDir) ?: [] as $f) {
    if (preg_match('/^photo_.+\.(jpe?g|jfif|webp)$/i', $f)) $files[] = $f;
}
sort($files);
if (!$files) { fwrite(STDERR, "[FATAL] No seed photos found in {$seedDir}\n"); exit(1); }
echo "Seed photos: " . count($files) . "\n";

$properties = $pdo->query(
    "SELECT id, slug, title FROM properties WHERE deleted_at IS NULL ORDER BY id"
)->fetchAll();
if (!$properties) { fwrite(STDERR, "[FATAL] No properties found.\n"); exit(1); }
echo "Properties: " . count($properties) . "\n";

$gd = extension_loaded('gd');

$totalInserted = 0; $totalSkipped = 0; $copiedFiles = 0; $thumbs = 0;

foreach ($properties as $prop) {
    $pid = (int) $prop['id'];

    $existing = (int) $pdo->query(
        "SELECT COUNT(*) FROM property_images WHERE property_id = {$pid} AND caption LIKE '" . CAPTION_PREFIX . "%'"
    )->fetchColumn();
    if ($existing >= count($files)) {
        $totalSkipped++;
        continue;
    }

    $hasPrimary = (bool) $pdo->query(
        "SELECT id FROM property_images WHERE property_id = {$pid} AND is_primary = 1 LIMIT 1"
    )->fetchColumn();
    $sort = (int) $pdo->query(
        "SELECT COALESCE(MAX(sort_order), -1) FROM property_images WHERE property_id = {$pid}"
    )->fetchColumn() + 1;

    $insertedHere = 0;

    foreach ($files as $i => $file) {
        $caption = CAPTION_PREFIX . ($i + 1);

        // Already has this exact seed photo? Skip.
        $dup = $pdo->prepare("SELECT id FROM property_images WHERE property_id = ? AND caption = ?");
        $dup->execute([$pid, $caption]);
        if ($dup->fetch()) continue;

        $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $ext  = $ext === 'jfif' ? 'jpg' : $ext;           // jfif == JPEG
        $name = "p{$pid}_img_seed" . ($i + 1) . ".{$ext}";

        $dest = $uploadDir . '/' . $name;
        if (!is_file($dest)) {
            if (!copy($seedDir . '/' . $file, $dest)) {
                echo "  [WARN] copy failed for p{$pid}: {$file}\n";
                continue;
            }
            $copiedFiles++;
        }

        // Thumbnail
        $thumbRel = null;
        if ($gd) {
            $thumbName = 'thumb_' . $name;
            $thumbRel  = 'uploads/properties/images/' . $thumbName;
            if (!is_file($uploadDir . '/' . $thumbName)) {
                $made = makeThumb($dest, $uploadDir . '/' . $thumbName, $ext);
                if ($made) { $thumbs++; } else { $thumbRel = null; }
            }
        }

        // Dimensions
        $w = $h = null;
        $dims = @getimagesize($dest);
        if ($dims) { $w = (int) $dims[0]; $h = (int) $dims[1]; }

        $isPrimary = (!$hasPrimary && $insertedHere === 0) ? 1 : 0;

        $stmt = $pdo->prepare(
            "INSERT INTO property_images
                (property_id, filename, path, thumbnail, category, caption,
                 sort_order, is_primary, file_size, width, height, uploaded_by, created_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())"
        );
        $stmt->execute([
            $pid,
            $name,
            'uploads/properties/images/' . $name,
            $thumbRel,
            'exterior',
            $caption,
            $sort++,
            $isPrimary,
            (int) filesize($dest),
            $w,
            $h,
            1, // admin
        ]);

        if ($isPrimary) $hasPrimary = true;
        $insertedHere++;
        $totalInserted++;
    }
    echo "  p{$pid} ({$prop['slug']}) +{$insertedHere}\n";
}

echo "\nDone. Inserted rows: {$totalInserted} | skipped properties (already seeded): {$totalSkipped} | files copied: {$copiedFiles} | thumbs: {$thumbs}\n";

function makeThumb(string $src, string $dest, string $ext): bool
{
    $info = @getimagesize($src);
    if (!$info) return false;
    [$ow, $oh, $type] = $info;
    try {
        $srcIm = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => imagecreatefrompng($src),
            IMAGETYPE_WEBP => imagecreatefromwebp($src),
            IMAGETYPE_GIF  => imagecreatefromgif($src),
            default        => null,
        };
    } catch (Throwable) {
        return false;
    }
    if (!$srcIm) return false;

    $ratio = min(400 / $ow, 300 / $oh);
    $nw = max(1, (int) round($ow * $ratio));
    $nh = max(1, (int) round($oh * $ratio));

    $dstIm = imagecreatetruecolor($nw, $nh);
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
        imagealphablending($dstIm, false);
        imagesavealpha($dstIm, true);
    }
    imagecopyresampled($dstIm, $srcIm, 0, 0, 0, 0, $nw, $nh, $ow, $oh);

    $ok = match ($type) {
        IMAGETYPE_JPEG => imagejpeg($dstIm, $dest, 85),
        IMAGETYPE_PNG  => imagepng($dstIm, $dest, 7),
        IMAGETYPE_WEBP => imagewebp($dstIm, $dest, 85),
        IMAGETYPE_GIF  => imagegif($dstIm, $dest),
        default        => false,
    };
    imagedestroy($srcIm);
    imagedestroy($dstIm);
    return (bool) $ok;
}
