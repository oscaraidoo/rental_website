<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class SettingsModel
{
    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $row = Database::fetchOne(
            "SELECT value, type FROM system_settings WHERE `key` = ?", [$key]
        );
        if (!$row) return $default;

        $val = self::cast($row['value'], $row['type']);
        self::$cache[$key] = $val;
        return $val;
    }

    public static function set(string $key, mixed $value): void
    {
        $existing = Database::fetchColumn(
            "SELECT id FROM system_settings WHERE `key` = ?", [$key]
        );
        if ($existing) {
            Database::update('system_settings', ['value' => (string) $value], '`key` = ?', [$key]);
        } else {
            Database::insert('system_settings', ['key' => $key, 'value' => (string) $value, 'type' => 'string']);
        }
        self::$cache[$key] = $value;
    }

    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            self::set($key, $value);
        }
    }

    public static function getGroup(string $group): array
    {
        $rows = Database::fetchAll(
            "SELECT `key`, value, type, label, description, is_public
             FROM system_settings WHERE `group` = ? ORDER BY id",
            [$group]
        );
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row;
            $result[$row['key']]['cast_value'] = self::cast($row['value'], $row['type']);
        }
        return $result;
    }

    public static function all(): array
    {
        $rows = Database::fetchAll(
            "SELECT `key`, value, type, `group`, label, description, is_public
             FROM system_settings ORDER BY `group`, id"
        );
        $grouped = [];
        foreach ($rows as $row) {
            $row['cast_value'] = self::cast($row['value'], $row['type']);
            $grouped[$row['group']][$row['key']] = $row;
        }
        return $grouped;
    }

    private static function cast(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int)    $value,
            'float'   => (float)  $value,
            'boolean' => (bool)   (int) $value,
            'json'    => json_decode($value, true) ?? [],
            default   => $value,
        };
    }

    /** Clear the in-memory cache */
    public static function flush(): void
    {
        self::$cache = [];
    }
}
