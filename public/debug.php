<?php
/**
 * Debug helper — shows PHP info, DB connection test and login flow test.
 * Access: https://rent.habellesfoundation.org/debug.php?key=habelles2026
 * DELETE immediately after use.
 */
if (($_GET['key'] ?? '') !== 'habelles2026') { http_response_code(403); die('Forbidden.'); }

define('ROOT_PATH', dirname(__DIR__));
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load .env
if (file_exists(ROOT_PATH . '/.env')) {
    foreach (file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
    }
}

echo '<style>body{font-family:system-ui;max-width:900px;margin:40px auto;padding:0 20px}
pre{background:#f5f5f5;padding:12px;border-radius:6px;overflow-x:auto;font-size:12px}
.ok{color:green;font-weight:700}.err{color:red;font-weight:700}
h2{margin:28px 0 8px;font-size:16px;font-weight:700;border-bottom:2px solid #eee;padding-bottom:4px}
</style>';
echo '<h1>Debug — RentalWebsite</h1>';

// 1. PHP version & extensions
echo '<h2>1. PHP Environment</h2>';
echo '<p>PHP: <strong>' . PHP_VERSION . '</strong></p>';
$exts = ['pdo_mysql','gd','fileinfo','curl','mbstring','intl','json','openssl'];
foreach ($exts as $ext) {
    $loaded = extension_loaded($ext);
    echo '<p>' . $ext . ': <span class="' . ($loaded ? 'ok' : 'err') . '">' . ($loaded ? '✓ loaded' : '✗ MISSING') . '</span></p>';
}

// 2. DB connection
echo '<h2>2. Database Connection</h2>';
try {
    $cfg = require ROOT_PATH . '/config/database.php';
    $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset={$cfg['charset']}";
    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo '<p class="ok">✓ Connected to ' . htmlspecialchars($cfg['database']) . ' on ' . htmlspecialchars($cfg['host']) . '</p>';

    // Check tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo '<p>Tables found: <strong>' . count($tables) . '</strong></p>';

    // Check users table has admin
    $admin = $pdo->query("SELECT id, email, status FROM users WHERE id=1")->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo '<p>Admin user: <strong>' . htmlspecialchars($admin['email']) . '</strong> — status: <strong>' . $admin['status'] . '</strong></p>';
    } else {
        echo '<p class="err">✗ No user with id=1 found</p>';
    }

    // Check roles
    $roles = $pdo->query("SELECT id, slug FROM roles")->fetchAll(PDO::FETCH_ASSOC);
    echo '<p>Roles: ' . implode(', ', array_column($roles, 'slug')) . '</p>';

    // Check properties
    $propCount = $pdo->query("SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL")->fetchColumn();
    $verified  = $pdo->query("SELECT COUNT(*) FROM properties WHERE status='verified' AND deleted_at IS NULL")->fetchColumn();
    echo '<p>Properties: <strong>' . $propCount . '</strong> total, <strong>' . $verified . '</strong> verified</p>';

} catch (\Throwable $e) {
    echo '<p class="err">✗ DB Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// 3. Session test
echo '<h2>3. Session</h2>';
try {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['_debug_test'] = 'ok';
    echo '<p class="ok">✓ Session started. ID: ' . session_id() . '</p>';
    echo '<p>Save path: ' . session_save_path() . '</p>';
    echo '<p>Session data keys: ' . implode(', ', array_keys($_SESSION) ?: ['(empty)']) . '</p>';
} catch (\Throwable $e) {
    echo '<p class="err">✗ Session error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// 4. File paths
echo '<h2>4. Paths & Permissions</h2>';
$paths = [
    'ROOT_PATH'          => ROOT_PATH,
    'storage/logs'       => ROOT_PATH . '/storage/logs',
    'storage/uploads'    => ROOT_PATH . '/storage/uploads',
    'public/uploads'     => ROOT_PATH . '/public/uploads',
    '.env'               => ROOT_PATH . '/.env',
];
foreach ($paths as $label => $path) {
    $exists   = file_exists($path);
    $writable = $exists && is_writable($path);
    echo '<p>' . $label . ': ' . ($exists ? '<span class="ok">exists</span>' : '<span class="err">MISSING</span>') .
         ($exists ? ' — ' . ($writable ? '<span class="ok">writable</span>' : '<span class="err">NOT writable</span>') : '') .
         '<br><small>' . htmlspecialchars($path) . '</small></p>';
}

// 5. Bootstrap test (full app load)
echo '<h2>5. Full Bootstrap Test</h2>';
try {
    require ROOT_PATH . '/bootstrap/app.php';
    echo '<p class="ok">✓ Bootstrap completed without error</p>';
    echo '<p>APP_URL: ' . htmlspecialchars($_ENV['APP_URL'] ?? 'not set') . '</p>';
    echo '<p>APP_ENV: ' . htmlspecialchars($_ENV['APP_ENV'] ?? 'not set') . '</p>';
} catch (\Throwable $e) {
    echo '<p class="err">✗ Bootstrap failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}

echo '<hr><p style="font-size:12px;color:#999">Delete this file immediately after use.</p>';
