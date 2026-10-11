<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ViewingModel;
use App\Helpers\{Request, Database};

class ViewingApiController extends BaseController
{
    /** GET /api/viewings */
    public function index(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $userId = (int) $_SESSION['user_id'];
        $role   = $_SESSION['user_role'] ?? '';

        $limit  = min(50, max(1, (int) Request::get('limit', 15)));
        $offset = max(0, (int) Request::get('offset', 0));

        if ($role === 'renter') {
            $viewings = ViewingModel::forRenter($userId, $limit, $offset);
            $total    = (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM viewing_requests WHERE renter_id = ?", [$userId]
            );
        } elseif ($role === 'landlord') {
            // Landlord module removed — legacy landlord accounts see an empty
            // list rather than falling into the see-everything branch below.
            $viewings = [];
            $total    = 0;
        } else {
            $viewings = ViewingModel::all($limit, $offset);
            $total    = ViewingModel::count();
        }

        $this->json([
            'success' => true,
            'data'    => $viewings,
            'total'   => $total,
        ]);
    }
}
