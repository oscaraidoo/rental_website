<?php
/**
 * Reset admin password + clear login lockouts
 * Visit: https://rent.habellesfoundation.org/reset_admin.php?key=habelles2026
 * DELETE immediately after use.
 */
if (($_GET['key'] ?? '') !== 'habelles2026') { http_response_code(403); die('Forbidden.'); }

if (!defined('ROOT_PATH')) { define('ROOT_PATH', dirname(__DIR__)); }

if (file_exists(ROOT_PATH . '/.env')) {
    foreach (file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
    }
}

require ROOT_PATH . '/bootstrap/app.php';
use App\Helpers\Database;

$newPassword = 'Admin@2026';
$hash        = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

Database::update('users', [
    'password'        => $hash,
    'login_attempts'  => 0,
    'locked_until'    => null,
    'status'          => 'active',
    'email_verified'  => 1,
], 'id = ?', [1]);

// Clear IP-based login rate limits / lockouts so the next login attempt is not
// blocked with "Too many failed attempts" — Security::isRateLimited() checks the
// rate_limits table BEFORE the password is verified, so a stale row would still
// lock the admin out even with the correct password.
$lockoutsCleared = 0;
try {
    $lockoutsCleared = Database::query("DELETE FROM rate_limits WHERE action = 'login'")->rowCount();
} catch (\Throwable $e) { /* table may be absent on older installs */ }

echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>body{font-family:system-ui;max-width:480px;margin:80px auto;padding:0 24px}
.box{background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:28px}
code{background:#fff;border:1px solid #d1fae5;padding:3px 8px;border-radius:4px;font-size:15px}
.del{margin-top:20px;padding:14px;background:#fff8f0;border:1.5px solid #fde68a;border-radius:8px;font-size:13px}
</style></head><body>
<div class="box">
    <h2 style="margin:0 0 16px">&#10003; Admin password reset</h2>
    <p><strong>Email:</strong> <code>admin@rentalwebsite.com</code></p>
    <p style="margin-top:10px"><strong>Password:</strong> <code>' . htmlspecialchars($newPassword) . '</code></p>
    <p style="margin-top:10px"><strong>Login lockouts cleared:</strong> <code>' . (int) $lockoutsCleared . '</code> rate-limit record(s) removed</p>
    <p style="margin-top:16px;font-size:13px;color:#555">Login at <a href="' . htmlspecialchars(rtrim($_ENV['APP_URL']??'','/') . '/auth/login') . '">/auth/login</a> — then change this password immediately.</p>
</div>
<div class="del">&#9888; <strong>Delete this file now</strong> — remove <code>public/reset_admin.php</code> from the server.</div>
</body></html>';
