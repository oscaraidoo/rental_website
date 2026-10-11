<?php use App\Helpers\{Url, Security, Str}; ?>

<form method="GET" class="row g-2 align-items-end mb-4">
    <div class="col-md-3"><label class="form-label fw-medium small">From</label><input type="date" name="date_from" class="form-control" value="<?= Security::e($dateFrom) ?>"></div>
    <div class="col-md-3"><label class="form-label fw-medium small">To</label><input type="date" name="date_to" class="form-control" value="<?= Security::e($dateTo) ?>"></div>
    <div class="col-md-2">
        <label class="form-label fw-medium small">Status</label>
        <select name="status" class="form-select">
            <option value="">All</option>
            <?php foreach (['confirmed','completed','cancelled','paid'] as $s): ?>
            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100 mt-3">Filter</button></div>
    <div class="col-md-2"><a href="<?= Url::base('admin/reports/export/viewings?date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo)) ?>" class="btn btn-outline-success w-100 mt-3"><i class="fa-solid fa-file-csv me-1"></i>Export</a></div>
</form>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php foreach (['total'=>['label'=>'Total','color'=>'primary'],'pending'=>['label'=>'Paid/Pending','color'=>'warning'],'confirmed'=>['label'=>'Confirmed','color'=>'info'],'completed'=>['label'=>'Completed','color'=>'success']] as $k => $info): ?>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-<?= $info['color'] ?> bg-opacity-10 text-<?= $info['color'] ?>"><i class="fa-solid fa-calendar-check"></i></div>
            <div><div class="stat-value"><?= number_format($stats[$k]) ?></div><div class="stat-label"><?= $info['label'] ?></div></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold border-0 py-3">
        Viewings Report <span class="badge bg-primary ms-2"><?= count($viewings) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead><tr><th>Reference</th><th>Property</th><th>Renter</th><th>Date</th><th>Fee</th><th>Paid</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (empty($viewings)): ?>
            <tr><td colspan="7" class="text-center text-muted py-5">No viewings in this range.</td></tr>
            <?php else: ?>
            <?php foreach ($viewings as $v): ?>
            <tr>
                <td><a href="<?= Url::base('admin/viewings/' . $v['id']) ?>"><code class="small"><?= Security::e($v['reference']) ?></code></a></td>
                <td class="small fw-medium"><?= Security::e(Str::truncate($v['property_title'], 28)) ?></td>
                <td class="small"><?= Security::e($v['renter_name']) ?></td>
                <td class="small"><?= date('d M Y', strtotime($v['preferred_date'])) ?></td>
                <td class="fw-semibold small"><?= Str::currency((float)$v['viewing_fee']) ?></td>
                <td class="text-center"><?= $v['fee_paid'] ? '<i class="fa-solid fa-check-circle text-success"></i>' : '<i class="fa-solid fa-clock text-warning"></i>' ?></td>
                <td><span class="badge-status status-<?= Security::e($v['status']) ?>"><?= ucwords(str_replace('_',' ',$v['status'])) ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
