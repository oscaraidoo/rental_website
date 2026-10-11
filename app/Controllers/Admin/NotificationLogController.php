<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Helpers\{Request, Paginator, Security};
use App\Services\{NotificationService, NotificationTemplates};

/**
 * Admin notification management — outbound history with filters + retry.
 */
class NotificationLogController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();

        $filters = [
            'channel' => in_array(Request::get('channel', ''), ['email', 'sms'], true) ? Request::get('channel') : null,
            'type'    => NotificationTemplates::has((string) Request::get('type', '')) ? Request::get('type') : null,
            'status'  => in_array(Request::get('status', ''), ['pending', 'sent', 'failed'], true) ? Request::get('status') : null,
        ];

        $total = NotificationService::historyCount($filters);
        $pager = Paginator::fromRequest($total, 25);
        $rows  = NotificationService::history($pager->perPage, $pager->offset, $filters);

        // Summary tiles
        $summary = \App\Helpers\Database::fetchOne(
            "SELECT COUNT(*) AS total,
                    SUM(status='sent') AS sent,
                    SUM(status='failed') AS failed,
                    SUM(status='pending') AS pending
             FROM notification_outbound"
        ) ?? [];

        $this->adminView('admin.notification_log', [
            'rows'      => $rows,
            'pager'     => $pager,
            'total'     => $total,
            'filters'   => $filters,
            'summary'   => $summary,
            'types'     => NotificationTemplates::TEMPLATES,
            'title'     => 'Notification Log',
            'breadcrumb'=> ['Notifications', 'Log'],
        ]);
    }

    /** POST /admin/notifications/log/:id/retry — retry a failed row once. */
    public function retry(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $ok = NotificationService::retry((int) $id);
        if ($ok) {
            $this->success('Notification re-sent successfully.');
        } else {
            $this->error('Retry failed. Check provider configuration; the failure has been logged.');
        }
        $this->redirect('admin/notifications/log');
    }
}
