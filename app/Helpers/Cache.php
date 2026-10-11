<?php
/**
 * Cache — tiny file-based TTL cache for slowly-changing reference data
 * (property types, regions, amenities, CMS lists).
 *
 * Stored in storage/cache/. Fail-safe: any storage problem just means
 * the value is recomputed. Serves stale-on-error? No — expired entries
 * are recomputed; unreadable files are treated as a miss.
 *
 * Note: reference data may lag admin edits by up to the TTL (default 300s).
 */

declare(strict_types=1);

namespace App\Helpers;

class Cache
{
    private static function dir(): string
    {
        $dir = ROOT_PATH . '/storage/cache';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        return $dir;
    }

    private static function file(string $key): string
    {
        return self::dir() . '/' . hash('sha256', $key) . '.cache';
    }

    /** Get a cached value or null if missing/expired */
    public static function get(string $key): mixed
    {
        $file = self::file($key);
        if (!is_file($file)) return null;
        $raw = @file_get_contents($file);
        if ($raw === false) return null;
        $data = @unserialize($raw);
        if (!is_array($data) || !isset($data['expires'], $data['value'])) return null;
        if ($data['expires'] < time()) { @unlink($file); return null; }
        return $data['value'];
    }

    /** Store a value with a TTL in seconds */
    public static function set(string $key, mixed $value, int $ttl = 300): void
    {
        $file = self::file($key);
        @file_put_contents($file, serialize(['expires' => time() + $ttl, 'value' => $value]), LOCK_EX);
    }

    /** Get-or-compute helper */
    public static function remember(string $key, int $ttl, callable $compute): mixed
    {
        $hit = self::get($key);
        if ($hit !== null) return $hit;
        $value = $compute();
        self::set($key, $value, $ttl);
        return $value;
    }

    /** Drop one key (or every key when $key is null) */
    public static function forget(?string $key = null): void
    {
        if ($key !== null) {
            @unlink(self::file($key));
            return;
        }
        foreach (glob(self::dir() . '/*.cache') ?: [] as $file) {
            @unlink($file);
        }
    }
}
