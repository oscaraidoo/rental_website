<?php
/**
 * Home Slide Model — hero slider slides managed from the admin CMS.
 * Table: home_slides (type image|video, media_path local 'uploads/...' or http(s) URL)
 */

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class HomeSlideModel
{
    /** All slides (admin list view) */
    public static function all(): array
    {
        return Database::fetchAll("SELECT * FROM home_slides ORDER BY sort_order, id");
    }

    /** Active slides for the public home page */
    public static function active(): array
    {
        return Database::fetchAll("SELECT * FROM home_slides WHERE active = 1 ORDER BY sort_order, id");
    }

    public static function find(int $id): ?array
    {
        return Database::fetchOne("SELECT * FROM home_slides WHERE id = ?", [$id]);
    }

    public static function create(array $data): int|string
    {
        return Database::insert('home_slides', $data);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('home_slides', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        Database::query("DELETE FROM home_slides WHERE id = ?", [$id]);
    }

    public static function count(): int
    {
        return (int) Database::fetchColumn("SELECT COUNT(*) FROM home_slides");
    }

    /** Swap sort_order with the neighbour in the given direction */
    public static function move(int $id, string $dir): bool
    {
        $slides     = self::all();
        $index      = null;
        foreach ($slides as $i => $s) {
            if ((int) $s['id'] === $id) { $index = $i; break; }
        }
        if ($index === null) return false;

        $target = $dir === 'up' ? $index - 1 : $index + 1;
        if ($target < 0 || $target >= count($slides)) return false;

        $a = $slides[$index];
        $b = $slides[$target];
        self::update((int) $a['id'], ['sort_order' => (int) $b['sort_order']]);
        self::update((int) $b['id'], ['sort_order' => (int) $a['sort_order']]);
        return true;
    }
}
