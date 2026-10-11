<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Helpers\{Database, Request, Security, Str};

class AmenityController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();
        $amenities = Database::fetchAll("SELECT * FROM amenities ORDER BY category, sort_order");
        $this->adminView('admin.amenities.index', compact('amenities') + [
            'title' => 'Amenities', 'breadcrumb' => ['Amenities'],
        ]);
    }

    public function store(): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $name = Security::sanitize(Request::post('name', ''));
        if (empty($name)) { $this->error('Name required.'); $this->redirect('admin/amenities'); }

        Database::insert('amenities', [
            'name'       => $name,
            'slug'       => Str::slug($name),
            'icon'       => Security::sanitize(Request::post('icon', 'fa-check')),
            'category'   => Security::sanitize(Request::post('category', 'indoor')),
            'sort_order' => (int) Request::post('sort_order', 99),
            'active'     => 1,
        ]);
        $this->success('Amenity added.');
        $this->redirect('admin/amenities');
    }

    public function update(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        Database::update('amenities', [
            'name'     => Security::sanitize(Request::post('name', '')),
            'icon'     => Security::sanitize(Request::post('icon', '')),
            'category' => Security::sanitize(Request::post('category', 'indoor')),
            'active'   => (int) !empty(Request::post('active')),
        ], 'id = ?', [(int) $id]);
        $this->success('Amenity updated.');
        $this->redirect('admin/amenities');
    }

    public function destroy(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $amenity = Database::fetchOne("SELECT name FROM amenities WHERE id = ?", [(int) $id]);
        if (!$amenity) $this->notFound();

        // Remove from property_amenities first, then delete
        Database::query("DELETE FROM property_amenities WHERE amenity_id = ?", [(int) $id]);
        Database::query("DELETE FROM amenities WHERE id = ?", [(int) $id]);
        $this->success("Amenity \"{$amenity['name']}\" deleted.");
        $this->redirect('admin/amenities');
    }
}
