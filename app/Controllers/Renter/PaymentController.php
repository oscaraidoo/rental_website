<?php

declare(strict_types=1);

namespace App\Controllers\Renter;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Helpers\Paginator;

class PaymentController extends BaseController
{
    public function index(): void
    {
        $this->renterOnly();

        $userId   = $this->currentUserId();
        $total    = (int) \App\Helpers\Database::fetchColumn("SELECT COUNT(*) FROM payments WHERE user_id = ?", [$userId]);
        $pager    = Paginator::fromRequest($total, 15);
        $payments = PaymentModel::forUser($userId, $pager->perPage, $pager->offset);

        $this->renterView('renter.payments.index', compact('payments', 'pager', 'total') + ['title' => 'Payment History']);
    }
}
