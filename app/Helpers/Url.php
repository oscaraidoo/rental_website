<?php
/**
 * URL Helper
 */

declare(strict_types=1);

namespace App\Helpers;

class Url
{
    private static string $base = '';

    public static function base(string $path = ''): string
    {
        if (self::$base === '') {
            $cfg = require ROOT_PATH . '/config/app.php';
            $url = $cfg['url'] ?? '';
            if ($url === '') {
                // Derive base URL from the current request if APP_URL not set
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $url    = $scheme . '://' . $host;
            }
            self::$base = rtrim($url, '/');
        }
        return self::$base . '/' . ltrim($path, '/');
    }

    /** Build a URL to a public asset */
    public static function asset(string $path): string
    {
        return self::base('assets/' . ltrim($path, '/'));
    }

    /** Build a URL to an uploaded file */
    public static function upload(string $path): string
    {
        return self::base('uploads/' . ltrim($path, '/'));
    }

    /**
     * Build a URL for a stored image path.
     * Absolute URLs (http/https) are passed through untouched; local paths
     * (e.g. 'uploads/properties/images/x.jpg') are prefixed with the base URL.
     */
    public static function image(string $path): string
    {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        return self::base($path);
    }

    /** Redirect to a URL */
    public static function redirect(string $url): never
    {
        if (!str_starts_with($url, 'http')) {
            $url = self::base($url);
        }
        // A redirect back to the exact page we're on is still a valid response:
        // re-serve the page (e.g. POST /auth/register redirecting to GET
        // /auth/register to show validation errors). Never exit silently —
        // that produced blank pages. PRG requires the Location header to be sent.
        header('Location: ' . $url, true, 302);
        exit;
    }

    /** Redirect back to the previous page */
    public static function back(string $fallback = '/'): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        $url = !empty($ref) ? $ref : self::base($fallback);
        header('Location: ' . $url, true, 302);
        exit;
    }

    /** Get current URL */
    public static function current(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /** Get current path */
    public static function path(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
    }

    /** Build a property URL */
    public static function property(array $property): string
    {
        // /properties/{region}/{city}/{slug}
        return self::base('properties/' . ($property['slug'] ?? $property['id']));
    }
}
