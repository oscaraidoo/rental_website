<?php
/**
 * Security Helper
 * CSRF, XSS, input sanitisation, rate limiting.
 */

declare(strict_types=1);

namespace App\Helpers;

class Security
{
    // ── CSRF ────────────────────────────────────────────────

    /** Generate (or return existing) CSRF token */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /** Render a hidden CSRF input field */
    public static function csrfField(): string
    {
        $token = self::csrfToken();
        return sprintf('<input type="hidden" name="_csrf_token" value="%s">', htmlspecialchars($token, ENT_QUOTES, 'UTF-8'));
    }

    /** Validate the submitted CSRF token */
    public static function verifyCsrf(): bool
    {
        $submitted = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($submitted) || empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $submitted);
    }

    /** Abort if CSRF check fails */
    public static function requireCsrf(): void
    {
        if (!self::verifyCsrf()) {
            http_response_code(403);
            die('Invalid CSRF token.');
        }
        // Regenerate token after use
        unset($_SESSION['_csrf_token']);
    }

    // ── XSS ─────────────────────────────────────────────────

    /** Escape output for HTML context */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** Sanitise a string (strip tags, trim) */
    public static function sanitize(string $input): string
    {
        return trim(strip_tags($input));
    }

    /** Sanitise an integer */
    public static function sanitizeInt(mixed $input): int
    {
        return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    /** Sanitise a float */
    public static function sanitizeFloat(mixed $input): float
    {
        return (float) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /** Sanitise an email */
    public static function sanitizeEmail(string $input): string
    {
        return strtolower(trim((string) filter_var($input, FILTER_SANITIZE_EMAIL)));
    }

    // ── Password ─────────────────────────────────────────────

    /** Hash a password */
    public static function hashPassword(string $password): string
    {
        $cost = (int) (require ROOT_PATH . '/config/app.php')['bcrypt_cost'];
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    /** Verify a password */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /** Check if password needs rehash */
    public static function needsRehash(string $hash): bool
    {
        $cost = (int) (require ROOT_PATH . '/config/app.php')['bcrypt_cost'];
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    // ── Tokens ───────────────────────────────────────────────

    /** Generate a cryptographically secure token */
    public static function generateToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /** Generate a URL-safe token */
    public static function generateUrlToken(int $bytes = 32): string
    {
        return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
    }

    // ── Rate Limiting ────────────────────────────────────────

    /**
     * Check if an action is rate-limited.
     * Returns true if the action is blocked.
     */
    public static function isRateLimited(string $key, int $maxAttempts = 5, int $decayMinutes = 30): bool
    {
        $action = explode(':', $key)[1] ?? $key;
        $row = Database::fetchOne(
            "SELECT attempts, blocked_until FROM rate_limits WHERE `key` = ? AND action = ?",
            [$key, $action]
        );
        if (!$row) return false;
        if ($row['blocked_until'] && strtotime($row['blocked_until']) > time()) {
            return true;
        }
        return false;
    }

    /** Record an attempt and optionally block */
    public static function recordAttempt(string $key, int $maxAttempts = 5, int $decayMinutes = 30): void
    {
        $parts = explode(':', $key, 2);
        $action = $parts[1] ?? $key;
        $existing = Database::fetchOne(
            "SELECT id, attempts, blocked_until FROM rate_limits WHERE `key` = ? AND action = ?",
            [$key, $action]
        );

        if (!$existing) {
            Database::query(
                "INSERT INTO rate_limits (`key`, action, attempts) VALUES (?, ?, 1)",
                [$key, $action]
            );
        } else {
            $attempts = $existing['attempts'] + 1;
            $blockedUntil = null;
            if ($attempts >= $maxAttempts) {
                $blockedUntil = date('Y-m-d H:i:s', time() + ($decayMinutes * 60));
            }
            Database::query(
                "UPDATE rate_limits SET attempts = ?, blocked_until = ? WHERE id = ?",
                [$attempts, $blockedUntil, $existing['id']]
            );
        }
    }

    /** Clear rate limit for a key */
    public static function clearRateLimit(string $key): void
    {
        $action = explode(':', $key)[1] ?? $key;
        Database::query("DELETE FROM rate_limits WHERE `key` = ? AND action = ?", [$key, $action]);
    }

    // ── Input ────────────────────────────────────────────────

    /** Get and sanitize a POST field */
    public static function post(string $key, mixed $default = null): mixed
    {
        if (!isset($_POST[$key])) return $default;
        $val = $_POST[$key];
        return is_string($val) ? self::sanitize($val) : $val;
    }

    /** Get and sanitize a GET field */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!isset($_GET[$key])) return $default;
        $val = $_GET[$key];
        return is_string($val) ? self::sanitize($val) : $val;
    }

    // ── IP ───────────────────────────────────────────────────

    public static function getIp(): string
    {
        $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }
}
