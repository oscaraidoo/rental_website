<?php
/**
 * HTTP Response Helper
 */

declare(strict_types=1);

namespace App\Helpers;

class Response
{
    /** Send a JSON response */
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Send a JSON success response */
    public static function success(mixed $data = null, string $message = 'Success', int $status = 200): never
    {
        self::json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    /** Send a JSON error response */
    public static function error(string $message = 'Error', int $status = 400, mixed $errors = null): never
    {
        self::json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }

    /** Send a 404 response */
    public static function notFound(string $message = 'Page not found'): never
    {
        http_response_code(404);
        // Load the 404 view if it exists
        $view = ROOT_PATH . '/app/Views/errors/404.php';
        if (file_exists($view)) {
            include $view;
        } else {
            echo "<h1>404 - $message</h1>";
        }
        exit;
    }

    /** Send a 403 response */
    public static function forbidden(string $message = 'Access denied'): never
    {
        http_response_code(403);
        $view = ROOT_PATH . '/app/Views/errors/403.php';
        if (file_exists($view)) {
            include $view;
        } else {
            echo "<h1>403 - $message</h1>";
        }
        exit;
    }

    /** Set a response header */
    public static function header(string $name, string $value): void
    {
        header("$name: $value");
    }

    /** Download a file */
    public static function download(string $filePath, string $filename): never
    {
        if (!file_exists($filePath)) {
            self::notFound('File not found');
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
