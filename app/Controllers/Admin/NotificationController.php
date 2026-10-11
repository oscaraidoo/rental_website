<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\NotificationBaseController;

class NotificationController extends NotificationBaseController
{
    protected function authOnly(): void   { $this->adminOnly(); }
    protected function routePrefix(): string { return 'admin/notifications'; }
    protected function viewName(): string { return 'admin.notifications.index'; }
    protected function pageTitle(): string { return 'Notifications'; }

    protected function viewWith(array $data): void
    {
        $data['breadcrumb'] = ['Notifications'];
        $this->adminView($this->viewName(), $data);
    }
}
