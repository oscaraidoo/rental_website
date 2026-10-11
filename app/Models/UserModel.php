<?php
/**
 * User Model
 */

declare(strict_types=1);

namespace App\Models;

use App\Helpers\{Database, Security, Str};

class UserModel
{
    // ── Finders ──────────────────────────────────────────────

    public static function findById(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND u.deleted_at IS NULL",
            [$id]
        );
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::fetchOne(
            "SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = ? AND u.deleted_at IS NULL",
            [strtolower(trim($email))]
        );
    }

    /** Find a user by their linked Google account id (sub claim). */
    public static function findByGoogleId(string $googleId): ?array
    {
        return Database::fetchOne(
            "SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.google_id = ? AND u.deleted_at IS NULL",
            [$googleId]
        );
    }

    /** Link (or re-link) a Google account to a user row. */
    public static function linkGoogle(int $id, string $googleId): void
    {
        // auth_provider records how the account was *created* (local vs google)
        // and is deliberately left untouched when linking an existing account.
        Database::update('users', ['google_id' => $googleId], 'id = ?', [$id]);
    }

    public static function existsByEmail(string $email): bool
    {
        // Only counts active (non-deleted) accounts.
        // Soft-deleted users can re-register with the same email.
        return (bool) Database::fetchColumn(
            "SELECT COUNT(*) FROM users WHERE email = ? AND deleted_at IS NULL",
            [strtolower(trim($email))]
        );
    }

    /** Paginated list for admin */
    public static function all(int $limit = 25, int $offset = 0, array $filters = []): array
    {
        $where  = ['u.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['role'])) {
            $where[]  = 'r.slug = ?';
            $params[] = $filters['role'];
        }
        if (!empty($filters['status'])) {
            $where[]  = 'u.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[]  = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
            $s = '%' . $filters['search'] . '%';
            array_push($params, $s, $s, $s, $s);
        }

        $sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.phone,
                       u.status, u.email_verified, u.created_at,
                       r.name AS role_name, r.slug AS role_slug
                FROM users u
                JOIN roles r ON r.id = u.role_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY u.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        return Database::fetchAll($sql, $params);
    }

    public static function count(array $filters = []): int
    {
        $where  = ['u.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['role'])) {
            $where[]  = 'r.slug = ?';
            $params[] = $filters['role'];
        }
        if (!empty($filters['status'])) {
            $where[]  = 'u.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[]  = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)';
            $s = '%' . $filters['search'] . '%';
            array_push($params, $s, $s, $s);
        }

        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE " . implode(' AND ', $where),
            $params
        );
    }

    // ── Create / Update ──────────────────────────────────────

    public static function create(array $data): int
    {
        $data['email']    = strtolower(trim($data['email']));
        $data['password'] = Security::hashPassword($data['password']);

        // If a soft-deleted row exists for this email, resurrect it instead of
        // inserting a new row (which would violate the UNIQUE key constraint).
        $existing = Database::fetchOne(
            "SELECT id, role_id FROM users WHERE email = ? AND deleted_at IS NOT NULL",
            [$data['email']]
        );

        if ($existing) {
            $existingId = (int) $existing['id'];

            // Reset the old row: clear deleted_at, apply all new values,
            // and wipe security-sensitive state so the account starts fresh
            // (a stale remember_token or verified flag must not carry over).
            $data['deleted_at']        = null;
            $data['remember_token']    = null;
            $data['login_attempts']    = 0;
            $data['locked_until']      = null;
            $data['last_login_at']     = null;
            $data['last_login_ip']     = null;
            $data['email_verified_at'] = null;
            if (!array_key_exists('email_verified', $data)) {
                $data['email_verified'] = 0;
            }

            Database::update('users', $data, 'id = ?', [$existingId]);

            // If the role changed, drop the old profile row so the new
            // role's profile can be created without a unique-key clash.
            if (isset($data['role_id']) && (int) $data['role_id'] !== (int) $existing['role_id']) {
                Database::query("DELETE FROM landlords WHERE user_id = ?", [$existingId]);
                Database::query("DELETE FROM renters WHERE user_id = ?", [$existingId]);
            }

            return $existingId;
        }

        return (int) Database::insert('users', $data);
    }

    public static function update(int $id, array $data): bool
    {
        if (isset($data['password'])) {
            $data['password'] = Security::hashPassword($data['password']);
        }
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }
        return Database::update('users', $data, 'id = ?', [$id]) > 0;
    }

    public static function softDelete(int $id): void
    {
        Database::update('users', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
    }

    // ── Login tracking ───────────────────────────────────────

    public static function recordLogin(int $id, string $ip): void
    {
        Database::update('users', [
            'last_login_at'  => date('Y-m-d H:i:s'),
            'last_login_ip'  => $ip,
            'login_attempts' => 0,
            'locked_until'   => null,
        ], 'id = ?', [$id]);
    }

    public static function incrementLoginAttempts(int $id): void
    {
        Database::query(
            "UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?",
            [$id]
        );
    }

    public static function lockAccount(int $id, int $minutes = 30): void
    {
        Database::update('users', [
            'locked_until' => date('Y-m-d H:i:s', time() + $minutes * 60),
        ], 'id = ?', [$id]);
    }

    public static function isLocked(array $user): bool
    {
        if (empty($user['locked_until'])) return false;
        return strtotime($user['locked_until']) > time();
    }

    // ── Email verification ───────────────────────────────────

    public static function createVerifyToken(int $userId): string
    {
        // Generate a 6-digit OTP for email verification
        $otp       = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minutes

        Database::query(
            "DELETE FROM email_verifications WHERE user_id = ?",
            [$userId]
        );
        Database::insert('email_verifications', [
            'user_id'    => $userId,
            'token'      => $otp,
            'expires_at' => $expiresAt,
        ]);
        return $otp;
    }

    public static function findVerifyToken(string $token): ?array
    {
        // Support both OTP (6 digits) and legacy link tokens
        return Database::fetchOne(
            "SELECT ev.*, u.email, u.first_name
             FROM email_verifications ev
             JOIN users u ON u.id = ev.user_id
             WHERE ev.token = ? AND ev.used = 0 AND ev.expires_at > NOW()",
            [$token]
        );
    }

    public static function markEmailVerified(int $userId): void
    {
        Database::update('users', [
            'email_verified'    => 1,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'status'            => 'active',
        ], 'id = ?', [$userId]);

        Database::query(
            "UPDATE email_verifications SET used = 1 WHERE user_id = ?",
            [$userId]
        );
    }

    // ── Password reset ────────────────────────────────────────

    public static function createPasswordReset(string $email): string
    {
        $token     = Security::generateToken(32);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hr

        Database::query("DELETE FROM password_resets WHERE email = ?", [$email]);
        Database::insert('password_resets', [
            'email'      => strtolower(trim($email)),
            'token'      => $token,
            'expires_at' => $expiresAt,
        ]);
        return $token;
    }

    public static function findPasswordReset(string $token): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM password_resets
             WHERE token = ? AND used = 0 AND expires_at > NOW()",
            [$token]
        );
    }

    public static function consumePasswordReset(string $token): void
    {
        Database::query(
            "UPDATE password_resets SET used = 1 WHERE token = ?",
            [$token]
        );
    }

    // ── Remember me ──────────────────────────────────────────

    public static function setRememberToken(int $id, string $token): void
    {
        Database::update('users', ['remember_token' => $token], 'id = ?', [$id]);
    }

    public static function findByRememberToken(string $token): ?array
    {
        return Database::fetchOne(
            "SELECT u.*, r.slug AS role_slug FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.remember_token = ? AND u.deleted_at IS NULL AND u.status = 'active'",
            [$token]
        );
    }

    // ── Role helpers ─────────────────────────────────────────

    public static function getRoleIdBySlug(string $slug): ?int
    {
        $id = Database::fetchColumn("SELECT id FROM roles WHERE slug = ?", [$slug]);
        return $id ? (int) $id : null;
    }

    // ── Stats ─────────────────────────────────────────────────

    public static function countByRole(string $roleSlug): int
    {
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE r.slug = ? AND u.deleted_at IS NULL",
            [$roleSlug]
        );
    }

    public static function recentRegistrations(int $days = 30): int
    {
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM users
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND deleted_at IS NULL",
            [$days]
        );
    }
}
