<?php

declare(strict_types=1);

namespace App\Controllers\Renter;

use App\Controllers\BaseController;
use App\Models\PropertyModel;
use App\Helpers\{Security, Request, Paginator, Database};

class SavedController extends BaseController
{
    public function index(): void
    {
        $this->renterOnly();

        $userId = $this->currentUserId();
        $total  = (int) Database::fetchColumn("SELECT COUNT(*) FROM saved_properties WHERE user_id = ?", [$userId]);
        $pager  = Paginator::fromRequest($total, 12);
        $saved  = PropertyModel::getSavedForUser($userId, $pager->perPage, $pager->offset);

        $this->renterView('renter.saved.index', compact('saved', 'pager', 'total') + ['title' => 'Saved Properties']);
    }

    /** Toggle save via AJAX or redirect */
    public function toggle(): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $propertyId = (int) Request::post('property_id', 0);
        if (!$propertyId) {
            if (Request::isAjax()) $this->jsonError('Invalid property.');
            $this->back();
        }

        $property = PropertyModel::findById($propertyId);
        if (!$property) {
            if (Request::isAjax()) $this->jsonError('Property not found.');
            $this->back();
        }

        $saved = PropertyModel::toggleSave($propertyId, $this->currentUserId());

        if (Request::isAjax()) {
            $this->jsonSuccess(['saved' => $saved, 'count' => $property['save_count'] + ($saved ? 1 : -1)],
                $saved ? 'Property saved.' : 'Property removed from saved.');
        }

        $this->success($saved ? 'Property saved to your list.' : 'Property removed from saved list.');
        $this->back('properties');
    }

    public function remove(string $id): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        Database::query(
            "DELETE FROM saved_properties WHERE property_id = ? AND user_id = ?",
            [(int) $id, $this->currentUserId()]
        );

        $this->success('Property removed from saved list.');
        $this->redirect('renter/saved');
    }
}
