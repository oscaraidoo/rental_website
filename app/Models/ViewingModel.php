<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\{Database, Str};

class ViewingModel
{
    public static function findById(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT vr.*,
                    p.title AS property_title, p.slug AS property_slug, p.reference AS property_ref,
                    p.rent_monthly, p.landlord_id,
                    CONCAT(u.first_name,' ',u.last_name) AS renter_name, u.email AS renter_email, u.phone AS renter_phone,
                    CONCAT(lu.first_name,' ',lu.last_name) AS landlord_name, lu.email AS landlord_email,
                    (SELECT pi.thumbnail FROM property_images pi WHERE pi.property_id = p.id AND pi.is_primary=1 LIMIT 1) AS property_thumb
             FROM viewing_requests vr
             JOIN properties p ON p.id = vr.property_id
             JOIN landlords ld ON ld.id = p.landlord_id
             JOIN users lu ON lu.id = ld.user_id
             JOIN users u  ON u.id = vr.renter_id
             WHERE vr.id = ?",
            [$id]
        );
    }

    public static function findByReference(string $ref): ?array
    {
        $id = Database::fetchColumn("SELECT id FROM viewing_requests WHERE reference = ?", [$ref]);
        return $id ? self::findById((int) $id) : null;
    }

    public static function forRenter(int $userId, int $limit = 20, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT vr.id, vr.reference, vr.preferred_date, vr.preferred_time,
                    vr.status, vr.viewing_fee, vr.fee_paid, vr.confirmed_date, vr.confirmed_time,
                    p.title AS property_title, p.slug AS property_slug,
                    ci.name AS city_name,
                    (SELECT pi.thumbnail FROM property_images pi WHERE pi.property_id = p.id AND pi.is_primary=1 LIMIT 1) AS property_thumb
             FROM viewing_requests vr
             JOIN properties p ON p.id = vr.property_id
             LEFT JOIN property_locations pl ON pl.property_id = p.id
             LEFT JOIN cities ci ON ci.id = pl.city_id
             WHERE vr.renter_id = ?
             ORDER BY vr.created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }

    public static function forLandlord(int $landlordId, int $limit = 20, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT vr.id, vr.reference, vr.preferred_date, vr.preferred_time,
                    vr.status, vr.viewing_fee, vr.fee_paid, vr.num_attendees,
                    p.title AS property_title, p.id AS property_id,
                    CONCAT(u.first_name,' ',u.last_name) AS renter_name, u.phone AS renter_phone
             FROM viewing_requests vr
             JOIN properties p ON p.id = vr.property_id
             JOIN users u ON u.id = vr.renter_id
             WHERE p.landlord_id = ?
             ORDER BY vr.created_at DESC LIMIT ? OFFSET ?",
            [$landlordId, $limit, $offset]
        );
    }

    public static function all(int $limit = 25, int $offset = 0, array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) { $where[] = 'vr.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['date_from'])) { $where[] = 'vr.preferred_date >= ?'; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to']))   { $where[] = 'vr.preferred_date <= ?'; $params[] = $filters['date_to']; }

        $params[] = $limit;
        $params[] = $offset;

        return Database::fetchAll(
            "SELECT vr.id, vr.reference, vr.preferred_date, vr.preferred_time, vr.status, vr.viewing_fee, vr.fee_paid,
                    p.title AS property_title,
                    CONCAT(u.first_name,' ',u.last_name) AS renter_name
             FROM viewing_requests vr
             JOIN properties p ON p.id = vr.property_id
             JOIN users u ON u.id = vr.renter_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY vr.created_at DESC LIMIT ? OFFSET ?",
            $params
        );
    }

    public static function count(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) { $where[] = 'vr.status = ?'; $params[] = $filters['status']; }
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM viewing_requests vr WHERE " . implode(' AND ', $where),
            $params
        );
    }

    public static function create(array $data): int
    {
        $data['reference'] = self::generateRef();
        return (int) Database::insert('viewing_requests', $data);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('viewing_requests', $data, 'id = ?', [$id]);
    }

    public static function getViewingFee(int $propertyId): float
    {
        // Check property-specific fee in system settings first
        $default = (float) (Database::fetchColumn(
            "SELECT value FROM system_settings WHERE `key` = 'viewing_fee_default'"
        ) ?? 50.00);
        return $default;
    }

    public static function statsForAdmin(): array
    {
        return [
            'total'     => (int) Database::fetchColumn("SELECT COUNT(*) FROM viewing_requests"),
            'pending'   => (int) Database::fetchColumn("SELECT COUNT(*) FROM viewing_requests WHERE status = 'paid'"),
            'confirmed' => (int) Database::fetchColumn("SELECT COUNT(*) FROM viewing_requests WHERE status = 'confirmed'"),
            'completed' => (int) Database::fetchColumn("SELECT COUNT(*) FROM viewing_requests WHERE status = 'completed'"),
        ];
    }

    private static function generateRef(): string
    {
        do {
            $ref = Str::generateReference('VW', 6);
            $exists = Database::fetchColumn("SELECT id FROM viewing_requests WHERE reference = ?", [$ref]);
        } while ($exists);
        return $ref;
    }
}
