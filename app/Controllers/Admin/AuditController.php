<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\AuditService;
use App\Helpers\{Request, Security, Paginator};

class AuditController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();

        $filters = [
            'module'    => Request::get('module', ''),
            'action'    => Request::get('action', ''),
            'date_from' => Request::get('date_from', ''),
            'date_to'   => Request::get('date_to', ''),
        ];

        $total  = AuditService::count($filters);
        $pager  = Paginator::fromRequest($total, 50);
        $logs   = AuditService::all($pager->perPage, $pager->offset, $filters);

        // Distinct modules for filter dropdown
        $modules = \App\Helpers\Database::fetchAll(
            "SELECT DISTINCT module FROM audit_logs ORDER BY module"
        );

        $this->adminView('admin.audit.index', compact('logs', 'filters', 'pager', 'total', 'modules') + [
            'title' => 'Audit Logs', 'breadcrumb' => ['Audit Logs'],
        ]);
    }
}
