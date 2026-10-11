<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Helpers\{Request, Security, Paginator};

class PaymentController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();

        $filters = [
            'status'    => Request::get('status', ''),
            'purpose'   => Request::get('purpose', ''),
            'date_from' => Request::get('date_from', ''),
            'date_to'   => Request::get('date_to', ''),
        ];

        $total    = PaymentModel::count($filters);
        $pager    = Paginator::fromRequest($total, 25);
        $payments = PaymentModel::all($pager->perPage, $pager->offset, $filters);

        $totalRevenue   = PaymentModel::totalRevenue();
        $monthRevenue   = PaymentModel::totalRevenue(date('Y-m-01'), date('Y-m-t'));
        $revenueBreakdown = PaymentModel::revenueByPurpose();

        $this->adminView('admin.payments.index', compact(
            'payments', 'filters', 'pager', 'total',
            'totalRevenue', 'monthRevenue', 'revenueBreakdown'
        ) + ['title' => 'Payments', 'breadcrumb' => ['Payments']]);
    }

    public function show(string $id): void
    {
        $this->adminOnly();
        $payment = PaymentModel::findById((int) $id);
        if (!$payment) $this->notFound();

        $this->adminView('admin.payments.show', compact('payment') + [
            'title' => 'Payment: ' . $payment['transaction_ref'],
            'breadcrumb' => ['Payments', $payment['transaction_ref']],
        ]);
    }
}
