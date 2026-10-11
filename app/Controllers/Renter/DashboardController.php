<?php

declare(strict_types=1);

namespace App\Controllers\Renter;

use App\Controllers\BaseController;
use App\Models\{PropertyModel, ViewingModel, PaymentModel};
use App\Services\NotificationService;
use App\Helpers\Database;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->renterOnly();

        $userId = $this->currentUserId();

        $stats = [
            'saved'          => (int) Database::fetchColumn("SELECT COUNT(*) FROM saved_properties WHERE user_id = ?", [$userId]),
            'compare'        => (int) Database::fetchColumn("SELECT COUNT(*) FROM property_comparisons WHERE user_id = ?", [$userId]),
            'viewings_total' => (int) Database::fetchColumn("SELECT COUNT(*) FROM viewing_requests WHERE renter_id = ?", [$userId]),
            'viewings_upcoming' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM viewing_requests WHERE renter_id = ? AND status = 'confirmed' AND confirmed_date >= CURDATE()",
                [$userId]
            ),
            'unread_notifications' => NotificationService::unreadCount($userId),
        ];

        $savedProperties  = PropertyModel::getSavedForUser($userId, 4);
        $recentViewings   = ViewingModel::forRenter($userId, 5);
        $recentPayments   = PaymentModel::forUser($userId, 3);

        $this->renterView('renter.dashboard', compact(
            'stats', 'savedProperties', 'recentViewings', 'recentPayments'
        ) + ['title' => 'My Dashboard']);
    }
}
