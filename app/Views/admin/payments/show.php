<?php use App\Helpers\{Url, Security, Str}; ?>
<div class="row justify-content-center">
<div class="col-12 col-lg-7">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold border-0 py-3">Payment: <?= Security::e($payment['transaction_ref']) ?></div>
        <div class="card-body">
            <div class="info-row"><span class="info-label">Transaction Ref</span><code><?= Security::e($payment['transaction_ref']) ?></code></div>
            <div class="info-row"><span class="info-label">User</span><?= Security::e($payment['user_name']) ?> &bull; <?= Security::e($payment['email']) ?></div>
            <div class="info-row"><span class="info-label">Amount</span><span class="fw-bold text-success fs-5"><?= Str::currency((float)$payment['amount']) ?></span></div>
            <div class="info-row"><span class="info-label">Currency</span><?= Security::e($payment['currency']) ?></div>
            <div class="info-row"><span class="info-label">Purpose</span><?= ucwords(str_replace('_',' ',$payment['purpose'])) ?></div>
            <div class="info-row"><span class="info-label">Gateway</span><?= Security::e($payment['gateway'] ?? '—') ?></div>
            <div class="info-row"><span class="info-label">Gateway Ref</span><code><?= Security::e($payment['gateway_ref'] ?? '—') ?></code></div>
            <div class="info-row"><span class="info-label">Status</span><span class="badge-status status-<?= Security::e($payment['status']) ?>"><?= ucfirst($payment['status']) ?></span></div>
            <div class="info-row"><span class="info-label">Payment Method</span><?= Security::e($payment['payment_method'] ?? '—') ?></div>
            <div class="info-row"><span class="info-label">Paid At</span><?= $payment['paid_at'] ? date('d M Y H:i', strtotime($payment['paid_at'])) : '—' ?></div>
            <div class="info-row"><span class="info-label">Created</span><?= date('d M Y H:i', strtotime($payment['created_at'])) ?></div>
            <?php if ($payment['notes']): ?>
            <div class="mt-3"><p class="text-muted small fw-medium mb-1">Notes</p><p><?= Security::e($payment['notes']) ?></p></div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            <a href="<?= Url::base('admin/payments') ?>" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to Payments
            </a>
        </div>
    </div>
</div>
</div>
