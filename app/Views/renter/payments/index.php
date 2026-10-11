<?php use App\Helpers\{Url, Security, Str}; ?>

<h5 class="fw-bold mb-4">Payment History</h5>

<?php if (empty($payments)): ?>
<div class="card border-0 shadow-sm text-center py-5">
    <i class="fa-solid fa-credit-card text-muted mb-3" style="font-size:3rem"></i>
    <h5 class="fw-bold">No payments yet</h5>
    <p class="text-muted">Your payment history will appear here.</p>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
                <tr><th>Reference</th><th>Purpose</th><th>Amount</th><th>Gateway</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><code class="small"><?= Security::e($p['transaction_ref']) ?></code></td>
                <td class="small"><?= ucwords(str_replace('_',' ',$p['purpose'])) ?></td>
                <td class="fw-semibold text-success"><?= Str::currency((float)$p['amount']) ?></td>
                <td class="small text-muted"><?= Security::e($p['gateway'] ?? 'N/A') ?></td>
                <td><span class="badge-status status-<?= Security::e($p['status']) ?>"><?= ucfirst($p['status']) ?></span></td>
                <td class="small text-muted"><?= date('d M Y H:i', strtotime($p['paid_at'] ?? $p['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pager->lastPage > 1): ?>
    <div class="card-footer bg-white border-0"><?= $pager->render() ?></div>
    <?php endif; ?>
</div>
<?php endif; ?>
