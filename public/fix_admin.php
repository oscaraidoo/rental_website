<?php
// Admin password reset — delete after use
define('ROOT_PATH', dirname(__DIR__));
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
$hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

// Reset admin + fix any column issues
Database::query("UPDATE users SET
    password = ?,
    status = 'active',
    login_attempts = 0,
    locked_until = NULL,
    email_verified = 1,
    deleted_at = NULL
    WHERE id = 1", [$hash]);

$user = Database::fetchOne("SELECT id, email, status, role_id FROM users WHERE id = 1");
echo '<pre>';
echo "Done!\n\n";
echo "Email:    " . ($user['email'] ?? 'NOT FOUND') . "\n";
echo "Password: " . $newPassword . "\n";
echo "Status:   " . ($user['status'] ?? '?') . "\n";
echo "Role ID:  " . ($user['role_id'] ?? '?') . "\n";
echo "\nDelete this file after logging in!";
echo '</pre>';
