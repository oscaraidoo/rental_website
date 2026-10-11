<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\{Request, Database};

/**
 * Shared notification-page behaviour (list, filter, mark read, preferences).
 * Concrete controllers set access control and the view/layout.
 */
abstract class NotificationBaseController extends BaseController
{
    /** Access control: must call $this->authOnly() in children. */
    abstract protected function authOnly(): void;

    /** Route prefix for mark-read forms: 'renter/notifications' or 'admin/notifications'. */
    abstract protected function routePrefix(): string;

    /** Layout view name: 'renter.notifications.index' etc. */
    abstract protected function viewName(): string;

    abstract protected function pageTitle(): string;

    public function index(): void
    {
        $this->authOnly();
        $userId = (int) $this->currentUserId();

        $total = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ?",
            [$userId]
        );
        $pager  = \App\Helpers\Paginator::fromRequest($total, 20);
        $notifications = \App\Services\NotificationService::forUser($userId, $pager->perPage, $pager->offset);
        $unread = \App\Services\NotificationService::unreadCount($userId);

        $this->viewWith([
            'notifications' => $notifications,
            'pager'         => $pager,
            'total'         => $total,
            'unread'        => $unread,
            'title'         => $this->pageTitle(),
        ]);
    }

    public function markRead(string $id): void
    {
        $this->authOnly();
        \App\Services\NotificationService::markRead((int) $id, (int) $this->currentUserId());
        if (Request::isAjax()) $this->jsonSuccess(null, 'Marked as read.');
        $this->redirect($this->routePrefix());
    }

    public function markAllRead(): void
    {
        $this->authOnly();
        \App\Services\NotificationService::markAllRead((int) $this->currentUserId());
        if (Request::isAjax()) $this->jsonSuccess(['unread' => 0], 'All notifications marked as read.');
        $this->success('All notifications marked as read.');
        $this->redirect($this->routePrefix());
    }

    public function destroy(string $id): void
    {
        $this->authOnly();
        $userId = (int) $this->currentUserId();
        // Only delete if it belongs to this user
        Database::query(
            "DELETE FROM notifications WHERE id = ? AND user_id = ?",
            [(int) $id, $userId]
        );
        $unread = \App\Services\NotificationService::unreadCount($userId);
        if (Request::isAjax()) $this->jsonSuccess(['unread' => $unread], 'Notification deleted.');
        $this->redirect($this->routePrefix());
    }

    /** The view wrapper used by children. */
    abstract protected function viewWith(array $data): void;
}
