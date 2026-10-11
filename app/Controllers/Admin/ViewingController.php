<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ViewingModel;
use App\Helpers\{Security, Request, Paginator, Database};
use App\Services\{AuditService, NotificationService, EmailService};

class ViewingController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();

        $filters = [
            'status'    => Request::get('status', ''),
            'date_from' => Request::get('date_from', ''),
            'date_to'   => Request::get('date_to', ''),
        ];

        $total    = ViewingModel::count($filters);
        $pager    = Paginator::fromRequest($total, 25);
        $viewings = ViewingModel::all($pager->perPage, $pager->offset, $filters);
        $stats    = ViewingModel::statsForAdmin();

        $this->adminView('admin.viewings.index', compact('viewings', 'filters', 'pager', 'total', 'stats') + [
            'title' => 'Viewing Requests', 'breadcrumb' => ['Viewings'],
        ]);
    }

    public function show(string $id): void
    {
        $this->adminOnly();
        $viewing = ViewingModel::findById((int) $id);
        if (!$viewing) $this->notFound();

        $this->adminView('admin.viewings.show', compact('viewing') + [
            'title' => 'Viewing: ' . $viewing['reference'], 'breadcrumb' => ['Viewings', $viewing['reference']],
        ]);
    }

    public function confirm(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $viewing = ViewingModel::findById((int) $id);
        if (!$viewing) $this->notFound();

        $date = Security::sanitize(Request::post('confirmed_date', $viewing['preferred_date']));
        $time = Security::sanitize(Request::post('confirmed_time', $viewing['preferred_time']));

        ViewingModel::update((int) $id, [
            'status'         => 'confirmed',
            'confirmed_date' => $date,
            'confirmed_time' => $time,
            'confirmed_by'   => $this->currentUserId(),
            'confirmed_at'   => date('Y-m-d H:i:s'),
        ]);

        NotificationService::viewingConfirmed((int) $viewing['renter_id'], $viewing['property_title'], $date, $time, (int) $id);
        EmailService::sendViewingConfirmed(
            $viewing['renter_email'],
            $viewing['renter_name'],
            array_merge($viewing, ['confirmed_date' => $date, 'confirmed_time' => $time])
        );

        AuditService::log('confirm_viewing', 'viewings', (int) $id, 'viewing');
        $this->success('Viewing confirmed and renter notified.');
        $this->redirect('admin/viewings');
    }

    public function cancel(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $viewing = ViewingModel::findById((int) $id);
        if (!$viewing) $this->notFound();

        $reason = Security::sanitize(Request::post('reason', ''));

        ViewingModel::update((int) $id, [
            'status'              => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_by'        => $this->currentUserId(),
            'cancelled_at'        => date('Y-m-d H:i:s'),
        ]);

        NotificationService::viewingCancelled((int) $viewing['renter_id'], $viewing['property_title'], (int) $id, $reason, false);

        AuditService::log('cancel_viewing', 'viewings', (int) $id, 'viewing', [], ['reason' => $reason]);
        $this->success('Viewing cancelled.');
        $this->redirect('admin/viewings');
    }

    public function destroy(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $viewing = ViewingModel::findById((int) $id);
        if (!$viewing) $this->notFound();

        // Only allow deletion of cancelled or no_show viewings
        if (!in_array($viewing['status'], ['cancelled', 'no_show', 'pending_payment'])) {
            $this->error('Only cancelled or pending-payment viewings can be deleted. Cancel it first.');
            $this->redirect('admin/viewings');
        }

        Database::query("DELETE FROM viewing_requests WHERE id = ?", [(int) $id]);
        AuditService::log('delete_viewing', 'viewings', (int) $id, 'viewing', $viewing, []);
        $this->success("Viewing request {$viewing['reference']} deleted.");
        $this->redirect('admin/viewings');
    }
}
