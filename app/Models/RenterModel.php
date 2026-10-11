<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class RenterModel
{
    public static function findByUserId(int $userId): ?array
    {
        return Database::fetchOne(
            "SELECT r.*, u.first_name, u.last_name, u.email, u.phone, u.avatar,
                    u.gender, u.date_of_birth, u.address, u.region, u.city,
                    u.status AS user_status, u.email_verified, u.email_verified_at,
                    u.phone_verified, u.last_login_at, u.auth_provider,
                    u.notification_prefs, u.two_factor_enabled, u.created_at AS user_created_at,
                    u.id AS user_id
             FROM renters r JOIN users u ON u.id = r.user_id
             WHERE r.user_id = ?",
            [$userId]
        );
    }

    public static function findOrCreateByUserId(int $userId): ?array
    {
        $existing = self::findByUserId($userId);
        if ($existing) return $existing;
        try {
            Database::insert('renters', ['user_id' => $userId]);
        } catch (\Throwable $e) {}
        return self::findByUserId($userId);
    }

    public static function findById(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT r.*, u.first_name, u.last_name, u.email, u.phone, u.avatar
             FROM renters r JOIN users u ON u.id = r.user_id WHERE r.id = ?",
            [$id]
        );
    }

    public static function create(int $userId): int
    {
        return (int) Database::insert('renters', ['user_id' => $userId]);
    }

    public static function update(int $userId, array $data): void
    {
        Database::update('renters', $data, 'user_id = ?', [$userId]);
    }
}
