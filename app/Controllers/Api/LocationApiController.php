<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Helpers\Database;

class LocationApiController extends BaseController
{
    /** GET /api/locations — top-level summary */
    public function index(): void
    {
        $regions = Database::fetchAll(
            "SELECT r.id, r.name, r.slug, COUNT(c.id) AS city_count
             FROM regions r
             LEFT JOIN cities c ON c.region_id = r.id AND c.active = 1
             WHERE r.active = 1
             GROUP BY r.id
             ORDER BY r.name"
        );
        $this->json(['success' => true, 'data' => $regions]);
    }

    /** GET /api/regions */
    public function regions(): void
    {
        $regions = Database::fetchAll(
            "SELECT id, name, slug FROM regions WHERE active = 1 ORDER BY name"
        );
        $this->json(['success' => true, 'data' => $regions]);
    }

    /** GET /api/cities/:regionId */
    public function cities(string $regionId): void
    {
        $cities = Database::fetchAll(
            "SELECT id, name, slug FROM cities
             WHERE region_id = ? AND active = 1
             ORDER BY name",
            [(int) $regionId]
        );
        $this->json(['success' => true, 'data' => $cities]);
    }

    /** GET /api/areas/:cityId */
    public function areas(string $cityId): void
    {
        $areas = Database::fetchAll(
            "SELECT id, name, slug, municipality FROM areas
             WHERE city_id = ? AND active = 1
             ORDER BY name",
            [(int) $cityId]
        );
        $this->json(['success' => true, 'data' => $areas]);
    }
}
