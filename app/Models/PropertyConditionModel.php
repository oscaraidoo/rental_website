<?php
/**
 * Property Condition Model — admin condition assessments for properties.
 * Table: property_conditions (multiple reports per property; newest active
 * one is shown publicly). Photos stored as JSON: [{path, caption}, ...]
 */

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class PropertyConditionModel
{
    public const LEVELS = ['excellent', 'good', 'fair', 'needs_repair', 'poor'];

    /** Human labels + badge colours for condition levels */
    public const LEVEL_META = [
        'excellent'    => ['label' => 'Excellent',    'color' => '#166534', 'bg' => '#f0fdf4'],
        'good'         => ['label' => 'Good',         'color' => '#235347', 'bg' => '#f0faf6'],
        'fair'         => ['label' => 'Fair',         'color' => '#92400e', 'bg' => '#fffbeb'],
        'needs_repair' => ['label' => 'Needs repair', 'color' => '#b45309', 'bg' => '#fef3c7'],
        'poor'         => ['label' => 'Poor',         'color' => '#991b1b', 'bg' => '#fef2f2'],
    ];

    /** Assessment aspects shown in the report (key => label) */
    public const ASPECTS = [
        'structure'      => 'Structure',
        'plumbing'       => 'Plumbing',
        'electrical'     => 'Electrical',
        'paint_finishing'=> 'Paint & finishing',
        'fittings'       => 'Fittings',
        'roof'           => 'Roof',
        'windows_doors'  => 'Windows & doors',
        'cleanliness'    => 'Cleanliness',
    ];

    public static function forProperty(int $propertyId): array
    {
        return Database::fetchAll(
            "SELECT pc.*, u.first_name, u.last_name
             FROM property_conditions pc
             JOIN users u ON u.id = pc.assessed_by
             WHERE pc.property_id = ?
             ORDER BY pc.active DESC, pc.assessment_date DESC, pc.id DESC",
            [$propertyId]
        );
    }

    /** Newest active report for the public property page (null if none) */
    public static function activeForProperty(int $propertyId): ?array
    {
        return Database::fetchOne(
            "SELECT pc.*, u.first_name, u.last_name
             FROM property_conditions pc
             JOIN users u ON u.id = pc.assessed_by
             WHERE pc.property_id = ? AND pc.active = 1
             ORDER BY pc.assessment_date DESC, pc.id DESC
             LIMIT 1",
            [$propertyId]
        );
    }

    public static function find(int $id): ?array
    {
        return Database::fetchOne("SELECT * FROM property_conditions WHERE id = ?", [$id]);
    }

    public static function create(array $data): int|string
    {
        return Database::insert('property_conditions', $data);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('property_conditions', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        Database::query("DELETE FROM property_conditions WHERE id = ?", [$id]);
    }

    /** Only one active report per property — activate $keepId, deactivate the rest */
    public static function makeOnlyActive(int $propertyId, int $keepId): void
    {
        Database::query(
            "UPDATE property_conditions SET active = (id = ?) WHERE property_id = ?",
            [$keepId, $propertyId]
        );
    }

    /** Decode the photos JSON safely */
    public static function photos(?string $json): array
    {
        if (empty($json)) return [];
        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }
}
