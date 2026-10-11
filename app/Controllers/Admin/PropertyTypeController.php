<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Helpers\{Database, Request, Security, Str};

class PropertyTypeController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();
        $types = Database::fetchAll("SELECT * FROM property_types ORDER BY sort_order");
        $this->adminView('admin.property_types.index', compact('types') + [
            'title' => 'Property Types', 'breadcrumb' => ['Property Types'],
        ]);
    }

    public function store(): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $name = Security::sanitize(Request::post('name', ''));
        if (empty($name)) { $this->error('Name required.'); $this->redirect('admin/property-types'); }

        Database::insert('property_types', [
            'name'       => $name,
            'slug'       => Str::slug($name),
            'icon'       => Security::sanitize(Request::post('icon', 'fa-home')),
            'sort_order' => (int) Request::post('sort_order', 99),
            'active'     => 1,
        ]);
        $this->success('Property type added.');
        $this->redirect('admin/property-types');
    }

    public function update(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        Database::update('property_types', [
            'name'       => Security::sanitize(Request::post('name', '')),
            'icon'       => Security::sanitize(Request::post('icon', '')),
            'sort_order' => (int) Request::post('sort_order', 0),
            'active'     => (int) !empty(Request::post('active')),
        ], 'id = ?', [(int) $id]);
        $this->success('Updated.');
        $this->redirect('admin/property-types');
    }

    public function destroy(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        // Block deletion if properties use this type
        $inUse = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM properties WHERE property_type_id = ? AND deleted_at IS NULL", [(int) $id]
        );
        if ($inUse > 0) {
            $this->error("Cannot delete — {$inUse} propert" . ($inUse === 1 ? 'y' : 'ies') . " use this type. Deactivate it instead.");
            $this->redirect('admin/property-types');
        }

        $type = Database::fetchOne("SELECT name FROM property_types WHERE id = ?", [(int) $id]);
        if (!$type) $this->notFound();

        Database::query("DELETE FROM property_types WHERE id = ?", [(int) $id]);
        $this->success("Property type \"{$type['name']}\" deleted.");
        $this->redirect('admin/property-types');
    }
}
