<?php
/**
 * Property Model
 * Core model covering all property queries.
 */

declare(strict_types=1);

namespace App\Models;

use App\Helpers\{Database, Str, Upload};

class PropertyModel
{
    // ── Finders ──────────────────────────────────────────────

    /** Shared full property SELECT (single JOIN query) — used by findById and findBySlug */
    private const FULL_SELECT =
        "SELECT p.*,
                pt.name AS type_name, pt.slug AS type_slug,
                ct.name AS ceiling_name, ft.name AS flooring_name,
                l.first_name AS landlord_first, l.last_name AS landlord_last,
                l.email AS landlord_email, l.phone AS landlord_phone,
                ld.verified AS landlord_verified, ld.id AS landlord_profile_id,
                ld.contact_phone AS landlord_contact_phone,
                ld.contact_phone2 AS landlord_contact_phone2,
                ld.contact_email AS landlord_contact_email,
                ld.contact_address AS landlord_contact_address,
                ld.contact_notes AS landlord_contact_notes,
                r.name AS region_name, ci.name AS city_name, a.name AS area_name
         FROM properties p
         JOIN landlords ld ON ld.id = p.landlord_id
         JOIN users l      ON l.id  = ld.user_id
         JOIN property_types pt ON pt.id = p.property_type_id
         LEFT JOIN ceiling_types ct ON ct.id = p.ceiling_type_id
         LEFT JOIN flooring_types ft ON ft.id = p.flooring_type_id
         LEFT JOIN property_locations pl ON pl.property_id = p.id
         LEFT JOIN regions r  ON r.id  = pl.region_id
         LEFT JOIN cities ci  ON ci.id = pl.city_id
         LEFT JOIN areas a    ON a.id  = pl.area_id";

    public static function findById(int $id, bool $withRelations = false): ?array
    {
        $prop = Database::fetchOne(
            self::FULL_SELECT . " WHERE p.id = ? AND p.deleted_at IS NULL",
            [$id]
        );

        if (!$prop || !$withRelations) return $prop;
        return self::attachRelations($prop);
    }

    public static function findBySlug(string $slug): ?array
    {
        // Single query — no separate id lookup (perf: property detail page)
        $prop = Database::fetchOne(
            self::FULL_SELECT . " WHERE p.slug = ? AND p.deleted_at IS NULL LIMIT 1",
            [$slug]
        );

        if (!$prop) return null;
        return self::attachRelations($prop);
    }

    /** Eager-load all related data onto a full property row */
    private static function attachRelations(array $prop): array
    {
        $id = (int) $prop['id'];

        $prop['location']      = self::getLocation($id);
        $prop['spaces']        = self::getSpaces($id);
        $prop['accessibility'] = self::getAccessibility($id);
        $prop['security']      = self::getSecurity($id);
        $prop['amenities']     = self::getAmenities($id);
        $prop['images']        = self::getImages($id);
        $prop['videos']        = self::getVideos($id);
        $prop['documents']     = self::getDocuments($id);
        $prop['verification']  = self::getVerification($id);

        return $prop;
    }

    public static function findByReference(string $ref): ?array
    {
        $id = Database::fetchColumn(
            "SELECT id FROM properties WHERE reference = ? AND deleted_at IS NULL",
            [$ref]
        );
        return $id ? self::findById((int) $id) : null;
    }

    // ── Search / Filter ───────────────────────────────────────

    public static function search(array $filters = [], int $limit = 12, int $offset = 0): array
    {
        [$where, $params, $joins] = self::buildSearchClauses($filters);

        $sql = "SELECT p.id, p.reference, p.title, p.slug, p.rent_monthly, p.rent_annual,
                       p.num_bedrooms, p.num_bathrooms, p.is_self_contained,
                       p.has_parking, p.water_available, p.electricity_type,
                       p.pet_policy, p.status, p.verified, p.is_featured, p.view_count,
                       p.furnished, p.building_level, p.available_from, p.created_at,
                       pt.name AS type_name, pt.slug AS type_slug,
                       pa.walking_time_minutes, pa.vehicle_accessible, pa.distance_main_road_km,
                       pl.region_id, pl.city_id, pl.area_id,
                       r.name AS region_name, ci.name AS city_name, a.name AS area_name,
                       (SELECT pi.path FROM property_images pi
                        WHERE pi.property_id = p.id AND pi.is_primary = 1 LIMIT 1) AS primary_image,
                       (SELECT pi.thumbnail FROM property_images pi
                        WHERE pi.property_id = p.id AND pi.is_primary = 1 LIMIT 1) AS primary_thumb
                FROM properties p
                JOIN landlords ld ON ld.id = p.landlord_id
                JOIN property_types pt ON pt.id = p.property_type_id
                LEFT JOIN property_locations pl ON pl.property_id = p.id
                LEFT JOIN property_accessibility pa ON pa.property_id = p.id
                LEFT JOIN regions r  ON r.id  = pl.region_id
                LEFT JOIN cities ci  ON ci.id = pl.city_id
                LEFT JOIN areas a    ON a.id  = pl.area_id
                {$joins}
                WHERE {$where}
                ORDER BY p.is_featured DESC, p.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        return Database::fetchAll($sql, $params);
    }

    public static function searchCount(array $filters = []): int
    {
        [$where, $params, $joins] = self::buildSearchClauses($filters);

        return (int) Database::fetchColumn(
            "SELECT COUNT(DISTINCT p.id)
             FROM properties p
             JOIN landlords ld ON ld.id = p.landlord_id
             JOIN property_types pt ON pt.id = p.property_type_id
             LEFT JOIN property_locations pl ON pl.property_id = p.id
             LEFT JOIN property_accessibility pa ON pa.property_id = p.id
             LEFT JOIN regions r  ON r.id  = pl.region_id
             LEFT JOIN cities ci  ON ci.id = pl.city_id
             LEFT JOIN areas a    ON a.id  = pl.area_id
             {$joins}
             WHERE {$where}",
            $params
        );
    }

    private static function buildSearchClauses(array $f): array
    {
        $where  = ["p.deleted_at IS NULL"];
        $params = [];
        $joins  = '';

        // Status – public search always shows only verified active properties
        $status = $f['status'] ?? 'verified';
        $where[]  = "p.status = ?";
        $params[] = $status;

        // Keyword
        if (!empty($f['q'])) {
            $where[]  = "(p.title LIKE ? OR p.description LIKE ? OR a.name LIKE ? OR ci.name LIKE ?)";
            $s = '%' . $f['q'] . '%';
            array_push($params, $s, $s, $s, $s);
        }

        // Location
        if (!empty($f['region_id'])) { $where[] = 'pl.region_id = ?'; $params[] = (int) $f['region_id']; }
        if (!empty($f['city_id']))   { $where[] = 'pl.city_id = ?';   $params[] = (int) $f['city_id'];   }
        if (!empty($f['area_id']))   { $where[] = 'pl.area_id = ?';   $params[] = (int) $f['area_id'];   }

        // Property type
        if (!empty($f['type_id']))   { $where[] = 'p.property_type_id = ?'; $params[] = (int) $f['type_id']; }

        // Price
        if (!empty($f['price_min'])) { $where[] = 'p.rent_monthly >= ?'; $params[] = (float) $f['price_min']; }
        if (!empty($f['price_max'])) { $where[] = 'p.rent_monthly <= ?'; $params[] = (float) $f['price_max']; }

        // Bedrooms
        if (isset($f['bedrooms']) && $f['bedrooms'] !== '') {
            if ($f['bedrooms'] === '4+') { $where[] = 'p.num_bedrooms >= 4'; }
            else                          { $where[] = 'p.num_bedrooms = ?'; $params[] = (int) $f['bedrooms']; }
        }
        if (!empty($f['bathrooms'])) { $where[] = 'p.num_bathrooms >= ?'; $params[] = (int) $f['bathrooms']; }

        // Boolean filters
        if (!empty($f['self_contained']))  { $where[] = 'p.is_self_contained = 1'; }
        if (!empty($f['parking']))         { $where[] = 'p.has_parking = 1'; }
        if (!empty($f['water']))           { $where[] = 'p.water_available = 1'; }
        if (!empty($f['furnished']))       { $where[] = "p.furnished = ?"; $params[] = $f['furnished']; }
        if (!empty($f['gated']))           {
            $joins .= " LEFT JOIN property_security ps ON ps.property_id = p.id ";
            $where[] = 'ps.gated = 1';
        }
        if (!empty($f['pets'])) { $where[] = "p.pet_policy = ?"; $params[] = $f['pets']; }

        // Electricity
        if (!empty($f['electricity'])) {
            $where[]  = "FIND_IN_SET(?, p.electricity_type)";
            $params[] = $f['electricity'];
        }

        // Walking time
        if (!empty($f['walk_max'])) {
            $where[]  = "(pa.walking_time_minutes IS NULL OR pa.walking_time_minutes <= ?)";
            $params[] = (int) $f['walk_max'];
        }

        // Vehicle accessible
        if (!empty($f['vehicle_access'])) { $where[] = 'pa.vehicle_accessible = 1'; }

        // Ceiling / flooring
        if (!empty($f['ceiling_type']))  { $where[] = 'p.ceiling_type_id = ?';  $params[] = (int) $f['ceiling_type']; }
        if (!empty($f['flooring_type'])) { $where[] = 'p.flooring_type_id = ?'; $params[] = (int) $f['flooring_type']; }

        // Building level
        if (!empty($f['building_level'])) { $where[] = "p.building_level = ?"; $params[] = $f['building_level']; }

        // Amenity filter (join)
        if (!empty($f['amenity_ids'])) {
            $ids = array_map('intval', (array) $f['amenity_ids']);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $joins .= " JOIN property_amenities pam ON pam.property_id = p.id ";
            $where[]  = "pam.amenity_id IN ({$placeholders})";
            array_push($params, ...$ids);
        }

        // Featured only
        if (!empty($f['featured'])) { $where[] = 'p.is_featured = 1 AND (p.featured_until IS NULL OR p.featured_until >= CURDATE())'; }

        return [implode(' AND ', $where), $params, $joins];
    }

    // ── Admin listing ─────────────────────────────────────────

    public static function all(int $limit = 25, int $offset = 0, array $filters = []): array
    {
        $where  = ['p.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['status']))    { $where[] = 'p.status = ?';    $params[] = $filters['status']; }
        if (!empty($filters['verified']))  { $where[] = 'p.verified = ?';  $params[] = (int) $filters['verified']; }
        if (!empty($filters['type_id']))   { $where[] = 'p.property_type_id = ?'; $params[] = (int) $filters['type_id']; }
        if (!empty($filters['landlord_id'])) { $where[] = 'p.landlord_id = ?'; $params[] = (int) $filters['landlord_id']; }
        if (!empty($filters['search'])) {
            $where[]  = "(p.title LIKE ? OR p.reference LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            array_push($params, $s, $s);
        }

        $params[] = $limit;
        $params[] = $offset;

        return Database::fetchAll(
            "SELECT p.id, p.reference, p.title, p.slug, p.rent_monthly, p.status, p.verified,
                    p.is_featured, p.view_count, p.submitted_at, p.created_at,
                    pt.name AS type_name,
                    CONCAT(u.first_name,' ',u.last_name) AS landlord_name,
                    ci.name AS city_name, r.name AS region_name
             FROM properties p
             JOIN landlords ld ON ld.id = p.landlord_id
             JOIN users u ON u.id = ld.user_id
             JOIN property_types pt ON pt.id = p.property_type_id
             LEFT JOIN property_locations pl ON pl.property_id = p.id
             LEFT JOIN cities ci ON ci.id = pl.city_id
             LEFT JOIN regions r ON r.id = pl.region_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY p.created_at DESC LIMIT ? OFFSET ?",
            $params
        );
    }    public static function count(array $filters = []): int
    {
        $where  = ['p.deleted_at IS NULL'];
        $params = [];
        // Mirrors all() so pagination totals match the filtered listing.
        if (!empty($filters['status']))      { $where[] = 'p.status = ?';           $params[] = $filters['status']; }
        if (!empty($filters['verified']))    { $where[] = 'p.verified = ?';         $params[] = (int) $filters['verified']; }
        if (!empty($filters['type_id']))     { $where[] = 'p.property_type_id = ?'; $params[] = (int) $filters['type_id']; }
        if (!empty($filters['landlord_id'])) { $where[] = 'p.landlord_id = ?';      $params[] = (int) $filters['landlord_id']; }
        if (!empty($filters['search'])) {
            $where[]  = "(p.title LIKE ? OR p.reference LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            array_push($params, $s, $s);
        }

        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM properties p WHERE " . implode(' AND ', $where),
            $params
        );
    }

    // ── CRUD ──────────────────────────────────────────────────

    public static function create(int $landlordId, array $data): int
    {
        $data['landlord_id'] = $landlordId;
        $data['reference']   = self::generateUniqueReference();
        $data['slug']        = self::generateUniqueSlug($data['title']);
        return (int) Database::insert('properties', $data);
    }

    public static function update(int $id, array $data): bool
    {
        // Auto-update slug if title changed
        if (isset($data['title'])) {
            $existing = Database::fetchColumn("SELECT title FROM properties WHERE id = ?", [$id]);
            if ($existing !== $data['title']) {
                $data['slug'] = self::generateUniqueSlug($data['title'], $id);
            }
        }
        return Database::update('properties', $data, 'id = ?', [$id]) >= 0;
    }

    public static function softDelete(int $id): void
    {
        Database::update('properties', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
    }

    // ── Image management (admin) ──────────────────────────

    public static function addImage(int $propertyId, array $data): int
    {
        $data['property_id'] = $propertyId;
        return (int) Database::insert('property_images', $data);
    }

    public static function setImagePrimary(int $propertyId, int $imageId): void
    {
        Database::query("UPDATE property_images SET is_primary = 0 WHERE property_id = ?", [$propertyId]);
        Database::query("UPDATE property_images SET is_primary = 1 WHERE id = ? AND property_id = ?", [$imageId, $propertyId]);
    }

    public static function deleteImage(int $propertyId, int $imageId): void
    {
        $img = Database::fetchOne(
            "SELECT * FROM property_images WHERE id = ? AND property_id = ?",
            [$imageId, $propertyId]
        );
        if (!$img) return;

        // Remove files (original + thumbnail) before dropping the row
        Upload::delete($img['path']);
        if (!empty($img['thumbnail']) && $img['thumbnail'] !== $img['path']) {
            Upload::delete($img['thumbnail']);
        }
        Database::query("DELETE FROM property_images WHERE id = ?", [$imageId]);

        // If the primary image was removed, promote the first remaining one
        if ((int) $img['is_primary'] === 1) {
            $next = Database::fetchColumn(
                "SELECT id FROM property_images WHERE property_id = ? ORDER BY sort_order, id LIMIT 1",
                [$propertyId]
            );
            if ($next) {
                Database::query("UPDATE property_images SET is_primary = 1 WHERE id = ?", [(int) $next]);
            }
        }
    }

    // ── Status management ─────────────────────────────────────

    public static function updateStatus(int $id, string $status, int $changedBy, string $reason = ''): void
    {
        $old = Database::fetchColumn("SELECT status FROM properties WHERE id = ?", [$id]);

        $data = ['status' => $status];
        if ($status === 'verified')  { $data['verified'] = 1; $data['verified_at'] = date('Y-m-d H:i:s'); $data['verified_by'] = $changedBy; $data['published_at'] = date('Y-m-d H:i:s'); }
        if ($status === 'rented')    { $data['rented_at'] = date('Y-m-d H:i:s'); }
        if ($status === 'submitted') { $data['submitted_at'] = date('Y-m-d H:i:s'); }
        if ($status === 'rejected')  { $data['rejection_reason'] = $reason; }

        Database::update('properties', $data, 'id = ?', [$id]);

        // Status history
        Database::insert('property_status_history', [
            'property_id' => $id,
            'old_status'  => $old,
            'new_status'  => $status,
            'changed_by'  => $changedBy,
            'reason'      => $reason,
        ]);
    }

    public static function incrementViewCount(int $id): void
    {
        Database::query("UPDATE properties SET view_count = view_count + 1 WHERE id = ?", [$id]);
    }

    // ── Related data getters ──────────────────────────────────

    public static function getLocation(int $propertyId): ?array
    {
        return Database::fetchOne(
            "SELECT pl.*, r.name AS region_name, ci.name AS city_name, a.name AS area_name
             FROM property_locations pl
             LEFT JOIN regions r ON r.id = pl.region_id
             LEFT JOIN cities ci ON ci.id = pl.city_id
             LEFT JOIN areas a ON a.id = pl.area_id
             WHERE pl.property_id = ?",
            [$propertyId]
        );
    }

    public static function saveLocation(int $propertyId, array $data): void
    {
        $exists = Database::fetchColumn(
            "SELECT id FROM property_locations WHERE property_id = ?", [$propertyId]
        );
        if ($exists) {
            Database::update('property_locations', $data, 'property_id = ?', [$propertyId]);
        } else {
            $data['property_id'] = $propertyId;
            Database::insert('property_locations', $data);
        }
    }

    public static function getSpaces(int $propertyId): array
    {
        return Database::fetchAll(
            "SELECT * FROM property_spaces WHERE property_id = ? ORDER BY sort_order, id",
            [$propertyId]
        );
    }

    public static function saveSpaces(int $propertyId, array $spaces): void
    {
        Database::query("DELETE FROM property_spaces WHERE property_id = ?", [$propertyId]);
        foreach ($spaces as $i => $space) {
            if (empty($space['space_name'])) continue;
            Database::insert('property_spaces', [
                'property_id' => $propertyId,
                'space_name'  => $space['space_name'],
                'space_type'  => $space['space_type'] ?? 'other',
                'length'      => $space['length']     ?: null,
                'width'       => $space['width']      ?: null,
                'height'      => $space['height']     ?: null,
                'unit'        => $space['unit']        ?? 'm',
                'notes'       => $space['notes']      ?? null,
                'sort_order'  => $i,
            ]);
        }
    }

    public static function getAccessibility(int $propertyId): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM property_accessibility WHERE property_id = ?", [$propertyId]
        );
    }

    public static function saveAccessibility(int $propertyId, array $data): void
    {
        $exists = Database::fetchColumn(
            "SELECT id FROM property_accessibility WHERE property_id = ?", [$propertyId]
        );
        if ($exists) {
            Database::update('property_accessibility', $data, 'property_id = ?', [$propertyId]);
        } else {
            $data['property_id'] = $propertyId;
            Database::insert('property_accessibility', $data);
        }
    }

    public static function getSecurity(int $propertyId): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM property_security WHERE property_id = ?", [$propertyId]
        );
    }

    public static function saveSecurity(int $propertyId, array $data): void
    {
        $exists = Database::fetchColumn(
            "SELECT id FROM property_security WHERE property_id = ?", [$propertyId]
        );
        if ($exists) {
            Database::update('property_security', $data, 'property_id = ?', [$propertyId]);
        } else {
            $data['property_id'] = $propertyId;
            Database::insert('property_security', $data);
        }
    }

    public static function getAmenities(int $propertyId): array
    {
        return Database::fetchAll(
            "SELECT a.id, a.name, a.slug, a.icon, a.category, pam.notes
             FROM property_amenities pam JOIN amenities a ON a.id = pam.amenity_id
             WHERE pam.property_id = ?",
            [$propertyId]
        );
    }

    public static function saveAmenities(int $propertyId, array $amenityIds): void
    {
        Database::query("DELETE FROM property_amenities WHERE property_id = ?", [$propertyId]);
        foreach (array_unique(array_map('intval', $amenityIds)) as $aid) {
            if ($aid <= 0) continue;
            Database::insert('property_amenities', ['property_id' => $propertyId, 'amenity_id' => $aid]);
        }
    }

    public static function getImages(int $propertyId): array
    {
        return Database::fetchAll(
            "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order, id",
            [$propertyId]
        );
    }

    public static function getPrimaryImage(int $propertyId): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM property_images WHERE property_id = ? AND is_primary = 1 LIMIT 1",
            [$propertyId]
        ) ?? Database::fetchOne(
            "SELECT * FROM property_images WHERE property_id = ? ORDER BY id LIMIT 1",
            [$propertyId]
        );
    }

    public static function getVideos(int $propertyId): array
    {
        return Database::fetchAll(
            "SELECT * FROM property_videos WHERE property_id = ? ORDER BY sort_order, id",
            [$propertyId]
        );
    }

    public static function getDocuments(int $propertyId): array
    {
        return Database::fetchAll(
            "SELECT * FROM property_documents WHERE property_id = ? ORDER BY id",
            [$propertyId]
        );
    }

    public static function getVerification(int $propertyId): ?array
    {
        return Database::fetchOne(
            "SELECT pv.*, CONCAT(u.first_name,' ',u.last_name) AS verifier_name
             FROM property_verifications pv
             LEFT JOIN users u ON u.id = pv.verified_by
             WHERE pv.property_id = ?
             ORDER BY pv.created_at DESC LIMIT 1",
            [$propertyId]
        );
    }

    public static function getStatusHistory(int $propertyId): array
    {
        return Database::fetchAll(
            "SELECT psh.*, CONCAT(u.first_name,' ',u.last_name) AS changed_by_name
             FROM property_status_history psh
             LEFT JOIN users u ON u.id = psh.changed_by
             WHERE psh.property_id = ?
             ORDER BY psh.created_at DESC",
            [$propertyId]
        );
    }

    // ── Saved properties ──────────────────────────────────────

    public static function isSavedByUser(int $propertyId, int $userId): bool
    {
        return (bool) Database::fetchColumn(
            "SELECT COUNT(*) FROM saved_properties WHERE property_id = ? AND user_id = ?",
            [$propertyId, $userId]
        );
    }

    public static function toggleSave(int $propertyId, int $userId): bool
    {
        $saved = self::isSavedByUser($propertyId, $userId);
        if ($saved) {
            Database::query("DELETE FROM saved_properties WHERE property_id = ? AND user_id = ?", [$propertyId, $userId]);
            Database::query("UPDATE properties SET save_count = GREATEST(0, save_count - 1) WHERE id = ?", [$propertyId]);
        } else {
            Database::insert('saved_properties', ['property_id' => $propertyId, 'user_id' => $userId]);
            Database::query("UPDATE properties SET save_count = save_count + 1 WHERE id = ?", [$propertyId]);
        }
        return !$saved;
    }

    public static function getSavedForUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT p.id, p.reference, p.title, p.slug, p.rent_monthly, p.status, p.verified,
                    pt.name AS type_name,
                    ci.name AS city_name, r.name AS region_name,
                    sp.saved_at,
                    (SELECT pi.thumbnail FROM property_images pi WHERE pi.property_id = p.id AND pi.is_primary=1 LIMIT 1) AS primary_thumb
             FROM saved_properties sp
             JOIN properties p ON p.id = sp.property_id
             JOIN property_types pt ON pt.id = p.property_type_id
             LEFT JOIN property_locations pl ON pl.property_id = p.id
             LEFT JOIN cities ci ON ci.id = pl.city_id
             LEFT JOIN regions r ON r.id = pl.region_id
             WHERE sp.user_id = ? AND p.deleted_at IS NULL
             ORDER BY sp.saved_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }

    // ── Compare ───────────────────────────────────────────────

    public static function getCompareList(int $userId): array
    {
        return Database::fetchAll(
            "SELECT p.id, p.reference, p.title, p.slug, p.status, p.rent_monthly, p.num_bedrooms,
                    p.num_bathrooms, p.has_parking, p.water_available, p.electricity_type,
                    p.is_self_contained, p.furnished, p.building_level, p.pet_policy,
                    p.ceiling_type_id, p.flooring_type_id,
                    pt.name AS type_name, ct.name AS ceiling_name, ft.name AS flooring_name,
                    ci.name AS city_name, r.name AS region_name,
                    pa.walking_time_minutes, pa.vehicle_accessible, pa.distance_main_road_km,
                    ps.gated,
                    (SELECT pi.thumbnail FROM property_images pi WHERE pi.property_id = p.id AND pi.is_primary=1 LIMIT 1) AS primary_thumb
             FROM property_comparisons pc
             JOIN properties p ON p.id = pc.property_id
             JOIN property_types pt ON pt.id = p.property_type_id
             LEFT JOIN ceiling_types ct ON ct.id = p.ceiling_type_id
             LEFT JOIN flooring_types ft ON ft.id = p.flooring_type_id
             LEFT JOIN property_locations pl ON pl.property_id = p.id
             LEFT JOIN property_accessibility pa ON pa.property_id = p.id
             LEFT JOIN property_security ps ON ps.property_id = p.id
             LEFT JOIN cities ci ON ci.id = pl.city_id
             LEFT JOIN regions r ON r.id = pl.region_id
             WHERE pc.user_id = ? AND p.deleted_at IS NULL",
            [$userId]
        );
    }

    public static function addToCompare(int $propertyId, int $userId): bool
    {
        $count = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM property_comparisons WHERE user_id = ?", [$userId]
        );
        if ($count >= 4) return false; // max 4 comparisons

        $exists = Database::fetchColumn(
            "SELECT id FROM property_comparisons WHERE property_id = ? AND user_id = ?",
            [$propertyId, $userId]
        );
        if (!$exists) {
            Database::insert('property_comparisons', ['property_id' => $propertyId, 'user_id' => $userId]);
        }
        return true;
    }

    public static function removeFromCompare(int $propertyId, int $userId): void
    {
        Database::query(
            "DELETE FROM property_comparisons WHERE property_id = ? AND user_id = ?",
            [$propertyId, $userId]
        );
    }

    // ── Dashboard stats ───────────────────────────────────────

    public static function statsForLandlord(int $landlordId): array
    {
        $total = Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE landlord_id = ? AND deleted_at IS NULL", [$landlordId]);
        $active = Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE landlord_id = ? AND status = 'verified' AND deleted_at IS NULL", [$landlordId]);
        $pending = Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE landlord_id = ? AND status IN ('submitted','under_review','inspection_required') AND deleted_at IS NULL", [$landlordId]);
        $rented = Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE landlord_id = ? AND status = 'rented' AND deleted_at IS NULL", [$landlordId]);

        return compact('total', 'active', 'pending', 'rented');
    }

    public static function platformStats(): array
    {
        return [
            'total'      => (int) Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL"),
            'verified'   => (int) Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE status='verified' AND deleted_at IS NULL"),
            'pending'    => (int) Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE status IN ('submitted','under_review') AND deleted_at IS NULL"),
            'rented'     => (int) Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE status='rented' AND deleted_at IS NULL"),
            'featured'   => (int) Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE is_featured=1 AND deleted_at IS NULL"),
        ];
    }

    // ── Reference / slug helpers ──────────────────────────────

    private static function generateUniqueReference(): string
    {
        do {
            $ref = Str::generateReference('RW', 6);
            $exists = Database::fetchColumn("SELECT id FROM properties WHERE reference = ?", [$ref]);
        } while ($exists);
        return $ref;
    }

    private static function generateUniqueSlug(string $title, int $excludeId = 0): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i    = 1;
        while (true) {
            $exists = Database::fetchColumn(
                "SELECT id FROM properties WHERE slug = ? AND id != ?",
                [$slug, $excludeId]
            );
            if (!$exists) break;
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
