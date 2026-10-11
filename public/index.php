<?php
/**
 * Front Controller
 * All HTTP requests are routed through this file.
 */

declare(strict_types=1);

// ── Constants ───────────────────────────────────────────────
define('ROOT_PATH', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);
define('START_TIME', microtime(true));

// ── Load .env ────────────────────────────────────────────────
if (file_exists(ROOT_PATH . '/.env')) {
    foreach (file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($val);
        }
    }
}

// ── Clear legacy custom session cookie (rw_session) if present ──
// This prevents redirect loops caused by a stale custom-named cookie
// from a previous bootstrap configuration.
if (!headers_sent() && isset($_COOKIE['rw_session'])) {
    setcookie('rw_session', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// ── Bootstrap ────────────────────────────────────────────────
$config = require ROOT_PATH . '/bootstrap/app.php';

// ── Router ───────────────────────────────────────────────────
require_once ROOT_PATH . '/routes/web.php';
