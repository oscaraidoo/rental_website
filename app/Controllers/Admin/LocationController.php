<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Helpers\{Database, Request, Security, Str};

class LocationController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();
        $regions = Database::fetchAll("SELECT r.*, COUNT(c.id) AS city_count FROM regions r LEFT JOIN cities c ON c.region_id = r.id GROUP BY r.id ORDER BY r.name");
        $cities  = Database::fetchAll("SELECT c.*, r.name AS region_name FROM cities c JOIN regions r ON r.id = c.region_id ORDER BY r.name, c.name LIMIT 100");
        $areas   = Database::fetchAll("SELECT a.*, c.name AS city_name FROM areas a JOIN cities c ON c.id = a.city_id ORDER BY c.name, a.name LIMIT 200");

        $this->adminView('admin.locations.index', compact('regions', 'cities', 'areas') + [
            'title' => 'Locations', 'breadcrumb' => ['Locations'],
        ]);
    }

    public function storeRegion(): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $name = Security::sanitize(Request::post('name', ''));
        if (empty($name)) { $this->error('Name required.'); $this->redirect('admin/locations'); }

        Database::insert('regions', ['name' => $name, 'slug' => Str::slug($name)]);
        $this->success('Region added.');
        $this->redirect('admin/locations');
    }

    public function storeCity(): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $name     = Security::sanitize(Request::post('name', ''));
        $regionId = (int) Request::post('region_id', 0);
        if (empty($name) || !$regionId) { $this->error('Name and region required.'); $this->redirect('admin/locations'); }

        Database::insert('cities', ['region_id' => $regionId, 'name' => $name, 'slug' => Str::slug($name)]);
        $this->success('City added.');
        $this->redirect('admin/locations');
    }

    public function storeArea(): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $name         = Security::sanitize(Request::post('name', ''));
        $cityId       = (int) Request::post('city_id', 0);
        $municipality = Security::sanitize(Request::post('municipality', ''));
        if (empty($name) || !$cityId) { $this->error('Name and city required.'); $this->redirect('admin/locations'); }

        Database::insert('areas', ['city_id' => $cityId, 'name' => $name, 'slug' => Str::slug($name), 'municipality' => $municipality]);
        $this->success('Area added.');
        $this->redirect('admin/locations');
    }

    public function deleteRegion(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        // Check if any properties reference this region
        $inUse = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM property_locations WHERE region_id = ?", [(int) $id]
        );
        if ($inUse > 0) {
            $this->error("Cannot delete — {$inUse} propert" . ($inUse === 1 ? 'y' : 'ies') . " reference this region.");
            $this->redirect('admin/locations');
        }

        $region = Database::fetchOne("SELECT name FROM regions WHERE id = ?", [(int) $id]);
        if (!$region) $this->notFound();

        Database::query("DELETE FROM regions WHERE id = ?", [(int) $id]);
        $this->success("Region \"{$region['name']}\" deleted.");
        $this->redirect('admin/locations');
    }

    public function deleteCity(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $inUse = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM property_locations WHERE city_id = ?", [(int) $id]
        );
        if ($inUse > 0) {
            $this->error("Cannot delete — {$inUse} propert" . ($inUse === 1 ? 'y' : 'ies') . " reference this city.");
            $this->redirect('admin/locations');
        }

        $city = Database::fetchOne("SELECT name FROM cities WHERE id = ?", [(int) $id]);
        if (!$city) $this->notFound();

        Database::query("DELETE FROM areas WHERE city_id = ?", [(int) $id]);
        Database::query("DELETE FROM cities WHERE id = ?", [(int) $id]);
        $this->success("City \"{$city['name']}\" and its areas deleted.");
        $this->redirect('admin/locations');
    }

    public function deleteArea(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $area = Database::fetchOne("SELECT name FROM areas WHERE id = ?", [(int) $id]);
        if (!$area) $this->notFound();

        Database::query("UPDATE property_locations SET area_id = NULL WHERE area_id = ?", [(int) $id]);
        Database::query("DELETE FROM areas WHERE id = ?", [(int) $id]);
        $this->success("Area \"{$area['name']}\" deleted.");
        $this->redirect('admin/locations');
    }
}
