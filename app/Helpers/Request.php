<?php
/**
 * HTTP Request Helper
 */

declare(strict_types=1);

namespace App\Helpers;

class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isPost(): bool  { return self::method() === 'POST'; }
    public static function isGet(): bool   { return self::method() === 'GET'; }
    public static function isAjax(): bool
    {
        return (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
            || (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json')
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    /** Get POST value with optional sanitization */
    public static function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /** Get GET / query string value */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /** Get value from POST first, then GET */
    public static function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /** Get all POST data */
    public static function all(): array
    {
        return $_POST;
    }

    /** Check if a POST or GET key exists */
    public static function has(string $key): bool
    {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }

    /** Get an uploaded file */
    public static function file(string $key): ?array
    {
        return (!empty($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE)
            ? $_FILES[$key]
            : null;
    }

    /** Parse JSON body */
    public static function json(): array
    {
        $body = file_get_contents('php://input');
        return json_decode($body, true) ?? [];
    }

    /** Get the current URI path */
    public static function uri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
    }

    /** Get a segment from the URI path */
    public static function segment(int $index): ?string
    {
        // Strip public sub-path if running under /rental_website/public/
        $uri  = self::uri();
        $base = parse_url((require ROOT_PATH . '/config/app.php')['url'], PHP_URL_PATH) ?? '';
        if ($base && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $segments = array_values(array_filter(explode('/', $uri)));
        return $segments[$index] ?? null;
    }
}
