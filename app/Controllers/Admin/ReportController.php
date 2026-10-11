<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\{PropertyModel, UserModel, ViewingModel, PaymentModel};
use App\Helpers\{Request, Security, Database};

class ReportController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();
        $this->adminView('admin.reports.index', ['title' => 'Reports', 'breadcrumb' => ['Reports']]);
    }

    public function properties(): void
    {
        $this->adminOnly();

        $dateFrom = Request::get('date_from', date('Y-m-01'));
        $dateTo   = Request::get('date_to',   date('Y-m-d'));

        $rows = Database::fetchAll(
            "SELECT p.id, p.reference, p.title, p.status, p.verified,
                    p.rent_monthly, p.created_at, p.published_at,
                    pt.name AS type_name,
                    CONCAT(u.first_name,' ',u.last_name) AS landlord_name,
                    ci.name AS city_name, r.name AS region_name
             FROM properties p
             JOIN landlords ld ON ld.id = p.landlord_id
             JOIN users u ON u.id = ld.user_id
             JOIN property_types pt ON pt.id = p.property_type_id
             LEFT JOIN property_locations pl ON pl.property_id = p.id
             LEFT JOIN cities ci ON ci.id = pl.city_id
             LEFT JOIN regions r ON r.id = pl.region_id
             WHERE p.created_at BETWEEN ? AND ? AND p.deleted_at IS NULL
             ORDER BY p.created_at DESC",
            [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
        );

        $this->adminView('admin.reports.properties', compact('rows', 'dateFrom', 'dateTo') + [
            'title' => 'Property Report', 'breadcrumb' => ['Reports', 'Properties'],
        ]);
    }

    public function revenue(): void
    {
        $this->adminOnly();

        $dateFrom = Request::get('date_from', date('Y-m-01'));
        $dateTo   = Request::get('date_to',   date('Y-m-d'));

        $payments = PaymentModel::all(500, 0, ['date_from' => $dateFrom, 'date_to' => $dateTo, 'status' => 'successful']);
        $total    = PaymentModel::totalRevenue($dateFrom, $dateTo);
        $breakdown = PaymentModel::revenueByPurpose();

        $this->adminView('admin.reports.revenue', compact('payments', 'total', 'breakdown', 'dateFrom', 'dateTo') + [
            'title' => 'Revenue Report', 'breadcrumb' => ['Reports', 'Revenue'],
        ]);
    }

    public function viewings(): void
    {
        $this->adminOnly();

        $dateFrom = Request::get('date_from', date('Y-m-01'));
        $dateTo   = Request::get('date_to',   date('Y-m-d'));
        $status   = Request::get('status', '');

        $filters = ['date_from' => $dateFrom, 'date_to' => $dateTo];
        if ($status) $filters['status'] = $status;

        $viewings = ViewingModel::all(500, 0, $filters);
        $stats    = ViewingModel::statsForAdmin();

        $this->adminView('admin.reports.viewings', compact('viewings', 'stats', 'dateFrom', 'dateTo', 'status') + [
            'title' => 'Viewings Report', 'breadcrumb' => ['Reports', 'Viewings'],
        ]);
    }

    public function export(string $type): void
    {
        $this->adminOnly();

        $dateFrom = Request::get('date_from', date('Y-m-01'));
        $dateTo   = Request::get('date_to',   date('Y-m-d'));

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');

        match ($type) {
            'properties' => $this->exportProperties($out, $dateFrom, $dateTo),
            'revenue'    => $this->exportRevenue($out, $dateFrom, $dateTo),
            'viewings'   => $this->exportViewings($out, $dateFrom, $dateTo),
            default      => fputcsv($out, ['Invalid export type']),
        };

        fclose($out);
        exit;
    }

    private function exportProperties($handle, string $from, string $to): void
    {
        fputcsv($handle, ['Reference', 'Title', 'Type', 'Status', 'Rent/Month', 'Landlord', 'City', 'Region', 'Created']);
        $rows = Database::fetchAll(
            "SELECT p.reference, p.title, pt.name, p.status, p.rent_monthly,
                    CONCAT(u.first_name,' ',u.last_name), ci.name, r.name, p.created_at
             FROM properties p
             JOIN landlords ld ON ld.id = p.landlord_id JOIN users u ON u.id = ld.user_id
             JOIN property_types pt ON pt.id = p.property_type_id
             LEFT JOIN property_locations pl ON pl.property_id = p.id
             LEFT JOIN cities ci ON ci.id = pl.city_id LEFT JOIN regions r ON r.id = pl.region_id
             WHERE p.created_at BETWEEN ? AND ? AND p.deleted_at IS NULL ORDER BY p.created_at DESC",
            [$from . ' 00:00:00', $to . ' 23:59:59']
        );
        foreach ($rows as $row) fputcsv($handle, array_values($row));
    }

    private function exportRevenue($handle, string $from, string $to): void
    {
        fputcsv($handle, ['Transaction Ref', 'User', 'Amount', 'Currency', 'Purpose', 'Gateway', 'Status', 'Date']);
        $rows = Database::fetchAll(
            "SELECT py.transaction_ref, CONCAT(u.first_name,' ',u.last_name), py.amount, py.currency,
                    py.purpose, py.gateway, py.status, py.paid_at
             FROM payments py JOIN users u ON u.id = py.user_id
             WHERE py.status = 'successful' AND py.paid_at BETWEEN ? AND ?
             ORDER BY py.paid_at DESC",
            [$from . ' 00:00:00', $to . ' 23:59:59']
        );
        foreach ($rows as $row) fputcsv($handle, array_values($row));
    }

    private function exportViewings($handle, string $from, string $to): void
    {
        fputcsv($handle, ['Reference', 'Property', 'Renter', 'Date', 'Time', 'Status', 'Fee', 'Paid', 'Requested']);
        $rows = Database::fetchAll(
            "SELECT vr.reference, p.title, CONCAT(u.first_name,' ',u.last_name),
                    vr.preferred_date, vr.preferred_time, vr.status, vr.viewing_fee, vr.fee_paid, vr.created_at
             FROM viewing_requests vr
             JOIN properties p ON p.id = vr.property_id JOIN users u ON u.id = vr.renter_id
             WHERE vr.created_at BETWEEN ? AND ? ORDER BY vr.created_at DESC",
            [$from . ' 00:00:00', $to . ' 23:59:59']
        );
        foreach ($rows as $row) fputcsv($handle, array_values($row));
    }
}
