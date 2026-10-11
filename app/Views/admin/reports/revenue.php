<?php use App\Helpers\{Url, Security, Str}; ?>

<form method="GET" class="row g-2 align-items-end mb-4">
    <div class="col-md-3"><label class="form-label fw-medium small">From</label><input type="date" name="date_from" class="form-control" value="<?= Security::e($dateFrom) ?>"></div>
    <div class="col-md-3"><label class="form-label fw-medium small">To</label><input type="date" name="date_to" class="form-control" value="<?= Security::e($dateTo) ?>"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    <div class="col-md-2"><a href="<?= Url::base('admin/reports/export/revenue?date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo)) ?>" class="btn btn-outline-success w-100"><i class="fa-solid fa-file-csv me-1"></i>Export CSV</a></div>
    <div class="col-md-2"><a href="<?= Url::base('admin/reports') ?>" class="btn btn-outline-secondary w-100">Back</a></div>
</form>

<!-- Summary -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fa-solid fa-coins"></i></div>
            <div><div class="stat-value"><?= Str::currency($total) ?></div><div class="stat-label">Total Revenue</div></div>
        </div>
    </div>
    <?php foreach ($breakdown as $b): ?>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fa-solid fa-chart-pie"></i></div>
            <div><div class="stat-value small"><?= Str::currency((float)$b['total']) ?></div><div class="stat-label"><?= ucwords(str_replace('_',' ',$b['purpose'])) ?> (<?= $b['count'] ?>)</div></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold border-0 py-3">
        Revenue Report — <?= Security::e($dateFrom) ?> to <?= Security::e($dateTo) ?>
        <span class="badge bg-primary ms-2"><?= count($payments) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead><tr><th>Ref</th><th>User</th><th>Amount</th><th>Purpose</th><th>Gateway</th><th>Date</th></tr></thead>
            <tbody>
            <?php if (empty($payments)): ?>
            <tr><td colspan="6" class="text-center text-muted py-5">No payments in this range.</td></tr>
            <?php else: ?>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><code class="small"><?= Security::e($p['transaction_ref']) ?></code></td>
                <td class="small"><?= Security::e($p['user_name']) ?></td>
                <td class="fw-semibold text-success"><?= Str::currency((float)$p['amount']) ?></td>
                <td class="small"><?= ucwords(str_replace('_',' ',$p['purpose'])) ?></td>
                <td class="small text-muted"><?= Security::e($p['gateway'] ?? '—') ?></td>
                <td class="small text-muted"><?= date('d M Y H:i', strtotime($p['paid_at'] ?? $p['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
