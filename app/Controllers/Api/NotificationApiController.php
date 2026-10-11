<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\NotificationService;
use App\Helpers\{Request, Database};

class NotificationApiController extends BaseController
{
    /** GET /api/notifications */
    public function index(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $userId = (int) $_SESSION['user_id'];

        // count_only mode for polling (navbar badge)
        if (Request::get('count_only')) {
            $this->json([
                'success' => true,
                'unread'  => NotificationService::unreadCount($userId),
            ]);
            return; // prevent fall-through
        }

        $limit  = min(50, max(1, (int) Request::get('limit', 20)));
        $offset = max(0, (int) Request::get('offset', 0));

        $notifications = NotificationService::forUser($userId, $limit, $offset);
        $unread        = NotificationService::unreadCount($userId);
        $total         = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ?", [$userId]
        );

        $this->json([
            'success' => true,
            'data'    => $notifications,
            'unread'  => $unread,
            'total'   => $total,
        ]);
    }

    /** POST /api/notifications/:id/read */
    public function markRead(string $id): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $userId = (int) $_SESSION['user_id'];
        NotificationService::markRead((int) $id, $userId);

        $this->json([
            'success' => true,
            'unread'  => NotificationService::unreadCount($userId),
        ]);
    }

    /** POST /api/notifications/read-all */
    public function markAllRead(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        \App\Helpers\Security::requireCsrf();
        $userId = (int) $_SESSION['user_id'];
        NotificationService::markAllRead($userId);

        $this->json([
            'success' => true,
            'unread'  => 0,
        ]);
    }

    /** POST /api/notifications/:id/delete */
    public function destroy(string $id): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $userId = (int) $_SESSION['user_id'];
        Database::query(
            "DELETE FROM notifications WHERE id = ? AND user_id = ?",
            [(int) $id, $userId]
        );

        $this->json([
            'success' => true,
            'unread'  => NotificationService::unreadCount($userId),
        ]);
    }
}
