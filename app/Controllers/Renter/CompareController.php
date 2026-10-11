<?php

declare(strict_types=1);

namespace App\Controllers\Renter;

use App\Controllers\BaseController;
use App\Models\PropertyModel;
use App\Helpers\{Security, Request};

class CompareController extends BaseController
{
    public function index(): void
    {
        $this->renterOnly();

        $userId     = $this->currentUserId();
        $properties = PropertyModel::getCompareList($userId);

        $this->renterView('renter.compare.index', compact('properties') + ['title' => 'Compare Properties']);
    }
}
