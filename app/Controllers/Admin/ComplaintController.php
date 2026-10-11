<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Helpers\{Database, Request, Security, Paginator};
use App\Services\AuditService;

class ComplaintController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();

        $status = Request::get('status', 'open');
        $total  = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM property_reports WHERE status = ?", [$status]
        );
        $pager  = Paginator::fromRequest($total, 25);

        $reports = Database::fetchAll(
            "SELECT pr.*, p.title AS property_title, p.reference,
                    CONCAT(u.first_name,' ',u.last_name) AS reporter_name
             FROM property_reports pr
             JOIN properties p ON p.id = pr.property_id
             LEFT JOIN users u ON u.id = pr.user_id
             WHERE pr.status = ?
             ORDER BY pr.created_at DESC LIMIT ? OFFSET ?",
            [$status, $pager->perPage, $pager->offset]
        );

        $this->adminView('admin.complaints.index', compact('reports', 'status', 'pager', 'total') + [
            'title' => 'Complaints & Reports', 'breadcrumb' => ['Complaints'],
        ]);
    }

    public function show(string $id): void
    {
        $this->adminOnly();
        $report = Database::fetchOne(
            "SELECT pr.*, p.title AS property_title, p.reference, p.id AS property_id,
                    CONCAT(u.first_name,' ',u.last_name) AS reporter_name, u.email AS reporter_email
             FROM property_reports pr
             JOIN properties p ON p.id = pr.property_id
             LEFT JOIN users u ON u.id = pr.user_id
             WHERE pr.id = ?",
            [(int) $id]
        );
        if (!$report) $this->notFound();

        $this->adminView('admin.complaints.show', compact('report') + [
            'title' => 'Complaint #' . $id, 'breadcrumb' => ['Complaints', '#' . $id],
        ]);
    }

    public function resolve(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $resolution = Security::sanitize(Request::post('resolution', ''));
        $status     = Security::sanitize(Request::post('status', 'resolved'));

        if (!in_array($status, ['resolved', 'dismissed', 'investigating'])) $status = 'resolved';

        Database::update('property_reports', [
            'status'      => $status,
            'resolution'  => $resolution,
            'resolved_by' => $this->currentUserId(),
            'resolved_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $id]);

        AuditService::log('resolve_complaint', 'complaints', (int) $id, 'report', [], ['status' => $status]);
        $this->success('Complaint updated.');
        $this->redirect('admin/complaints');
    }
}
