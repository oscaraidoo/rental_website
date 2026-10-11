<?php
/**
 * Application Bootstrap
 * Loaded by public/index.php before anything else.
 */

declare(strict_types=1);

// ── Timezone ────────────────────────────────────────────────
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Africa/Accra');

// ── Error reporting ─────────────────────────────────────────
$debug = filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN);
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// ── Global error handling (production) ──────────────────────
if (!$debug) {
    // Try writing logs to storage/logs/, fall back to system temp
    $logDir = ROOT_PATH . '/storage/logs';
    if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }
    $logFile = is_writable($logDir) ? $logDir . '/php_error.log' : sys_get_temp_dir() . '/rw_error.log';
    ini_set('error_log', $logFile);

    set_exception_handler(function (\Throwable $e): void {
        error_log(sprintf(
            'Uncaught %s: %s in %s:%d',
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));
        \App\Services\PerformanceService::recordError('exception', get_class($e) . ': ' . $e->getMessage());
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        $errorView = ROOT_PATH . '/app/Views/errors/500.php';
        if (is_file($errorView)) {
            include $errorView;
        } else {
            echo '<h1>500 - Internal Server Error</h1><p>Something went wrong. Please try again later.</p>';
        }
        exit;
    });

    register_shutdown_function(function (): void {
        $err = error_get_last();
        if ($err !== null && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            \App\Services\PerformanceService::recordError('fatal', ($err['message'] ?? '') );
            if (!headers_sent()) {
                http_response_code(500);
            }
            $errorView = ROOT_PATH . '/app/Views/errors/500.php';
            if (is_file($errorView)) {
                include $errorView; // sent after output ends — best effort
            }
        }
    });
}

// ── Autoloader ───────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $prefixMap = [
        'App\\' => ROOT_PATH . '/app/',
    ];
    foreach ($prefixMap as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($class, $prefix, $len) === 0) {
            $relClass = substr($class, $len);
            $file     = $baseDir . str_replace('\\', '/', $relClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// ── Performance monitoring (lightweight, no personal data) ────
// Records one perf_log row per request. Enabled when APP_DEBUG=false
// or when PERF_MONITOR=true is set in .env (for local measurement).
\App\Services\PerformanceService::begin();
register_shutdown_function([\App\Services\PerformanceService::class, 'finish']);

// ── Session ──────────────────────────────────────────────────
// Use PHPSESSID (default) to avoid cookie mismatch on sub-directory installs.
// The cookie path is set to '/' so it works across the whole site.
if (session_status() === PHP_SESSION_NONE) {
    // Only set cookie params before session_start()
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', (string)(60 * 60 * 24)); // 24 hours
    session_set_cookie_params([
        'lifetime' => 0,        // session cookie (expires when browser closes)
        'path'     => '/',
        'domain'   => '',
        // HTTPS-only in production (APP_DEBUG=false), relaxed for local dev.
        'secure'   => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN) ? false : true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── Config ────────────────────────────────────────────────────
$config = require ROOT_PATH . '/config/app.php';

// ── Database connection ──────────────────────────────────────
require_once ROOT_PATH . '/app/Helpers/Database.php';

// ── Request helpers ───────────────────────────────────────────
require_once ROOT_PATH . '/app/Helpers/Request.php';
require_once ROOT_PATH . '/app/Helpers/Response.php';
require_once ROOT_PATH . '/app/Helpers/Url.php';
require_once ROOT_PATH . '/app/Helpers/Security.php';
require_once ROOT_PATH . '/app/Helpers/Flash.php';
require_once ROOT_PATH . '/app/Helpers/Str.php';
require_once ROOT_PATH . '/app/Helpers/Upload.php';
require_once ROOT_PATH . '/app/Helpers/Paginator.php';

return $config;
