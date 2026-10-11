<?php use App\Helpers\{Url, Security, Str}; ?>

<!-- Date filter -->
<form method="GET" class="row g-2 align-items-end mb-4">
    <div class="col-md-3"><label class="form-label fw-medium small">From</label><input type="date" name="date_from" class="form-control" value="<?= Security::e($dateFrom) ?>"></div>
    <div class="col-md-3"><label class="form-label fw-medium small">To</label><input type="date" name="date_to" class="form-control" value="<?= Security::e($dateTo) ?>"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    <div class="col-md-2"><a href="<?= Url::base('admin/reports/export/properties?date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo)) ?>" class="btn btn-outline-success w-100"><i class="fa-solid fa-file-csv me-1"></i>Export CSV</a></div>
    <div class="col-md-2"><a href="<?= Url::base('admin/reports') ?>" class="btn btn-outline-secondary w-100">Back</a></div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold border-0 py-3">
        Property Report — <?= Security::e($dateFrom) ?> to <?= Security::e($dateTo) ?>
        <span class="badge bg-primary ms-2"><?= count($rows) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
                <tr><th>Reference</th><th>Title</th><th>Type</th><th>Landlord</th><th>City</th><th>Rent</th><th>Status</th><th>Verified</th><th>Created</th></tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
            <tr><td colspan="9" class="text-center text-muted py-5">No properties in this date range.</td></tr>
            <?php else: ?>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><a href="<?= Url::base('admin/properties/' . $r['id']) ?>"><code class="small"><?= Security::e($r['reference']) ?></code></a></td>
                <td class="small fw-medium"><?= Security::e(Str::truncate($r['title'], 30)) ?></td>
                <td class="small"><?= Security::e($r['type_name']) ?></td>
                <td class="small"><?= Security::e($r['landlord_name']) ?></td>
                <td class="small text-muted"><?= Security::e($r['city_name'] ?? '—') ?></td>
                <td class="fw-semibold small"><?= Str::currency((float)$r['rent_monthly']) ?></td>
                <td><span class="badge-status status-<?= Security::e($r['status']) ?>"><?= ucwords(str_replace('_',' ',$r['status'])) ?></span></td>
                <td class="text-center"><?= $r['verified'] ? '<i class="fa-solid fa-check-circle text-success"></i>' : '<i class="fa-solid fa-times-circle text-muted"></i>' ?></td>
                <td class="small text-muted"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
