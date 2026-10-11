<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\{UserModel, PropertyModel, ViewingModel, PaymentModel};
use App\Helpers\Database;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();

        // ── Stats ────────────────────────────────────────────
        $stats = [
            'total_properties'  => PropertyModel::count(),
            'verified'          => PropertyModel::count(['verified' => 1]),
            'pending'           => PropertyModel::count(['status' => 'submitted']) + PropertyModel::count(['status' => 'under_review']),
            'rented'            => PropertyModel::count(['status' => 'rented']),
            'total_landlords'   => UserModel::countByRole('landlord'),
            'total_renters'     => UserModel::countByRole('renter'),
            'viewings_total'    => ViewingModel::count(),
            'viewings_confirmed'=> ViewingModel::count(['status' => 'confirmed']),
            'viewings_completed'=> ViewingModel::count(['status' => 'completed']),
            'revenue_total'     => PaymentModel::totalRevenue(),
            'revenue_month'     => PaymentModel::totalRevenue(date('Y-m-01'), date('Y-m-t')),
            'new_users_30d'     => UserModel::recentRegistrations(30),
        ];

        // ── Chart data (last 6 months) ────────────────────────
        $chartData = $this->buildChartData();

        // ── Recent activity ───────────────────────────────────
        $recentProperties = PropertyModel::all(5, 0, ['status' => 'submitted']);
        $recentViewings   = ViewingModel::all(5, 0);
        $recentPayments   = PaymentModel::all(5, 0, ['status' => 'successful']);

        $this->adminView('admin.dashboard', compact(
            'stats', 'chartData', 'recentProperties', 'recentViewings', 'recentPayments'
        ) + ['title' => 'Admin Dashboard', 'breadcrumb' => ['Dashboard']]);
    }

    private function buildChartData(): array
    {
        $months = [];
        $revenueValues    = [];
        $propertyValues   = [];

        for ($i = 5; $i >= 0; $i--) {
            $date     = date('Y-m', strtotime("-{$i} months"));
            $months[] = date('M Y', strtotime("-{$i} months"));

            $from = $date . '-01';
            $to   = date('Y-m-t', strtotime($from));

            $revenueValues[]  = PaymentModel::totalRevenue($from, $to);
            $propertyValues[] = (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM properties WHERE DATE(created_at) BETWEEN ? AND ?",
                [$from, $to]
            );
        }

        $statusData = PropertyModel::platformStats();

        return [
            'revenue'    => ['labels' => $months, 'values' => $revenueValues],
            'properties' => ['labels' => $months, 'values' => $propertyValues],
            'statuses'   => [
                'labels' => ['Verified', 'Pending', 'Rented', 'Draft', 'Rejected'],
                'values' => [
                    $statusData['verified'],
                    $statusData['pending'],
                    $statusData['rented'],
                    PropertyModel::count(['status' => 'draft']),
                    PropertyModel::count(['status' => 'rejected']),
                ],
            ],
        ];
    }
}
