<?php use App\Helpers\{Url, Security, Str}; ?>

<!-- Summary stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px">
    <div class="qs-card">
        <div class="qs-card-label">Total Revenue</div>
        <div class="qs-card-value"><?= Str::currency($totalRevenue) ?></div>
        <div class="qs-card-accent"></div>
    </div>
    <div class="qs-card">
        <div class="qs-card-label">This Month</div>
        <div class="qs-card-value"><?= Str::currency($monthRevenue) ?></div>
        <div class="qs-card-accent"></div>
    </div>
    <?php foreach (array_slice($revenueBreakdown, 0, 3) as $rb): ?>
    <div class="qs-card">
        <div class="qs-card-label"><?= ucwords(str_replace('_', ' ', $rb['purpose'])) ?></div>
        <div class="qs-card-value" style="font-size:20px"><?= Str::currency((float)$rb['total']) ?></div>
        <div class="qs-card-sub"><?= $rb['count'] ?> transactions</div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="table-card mb-4">
    <div style="padding:16px 20px">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
            <div style="min-width:140px">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php foreach (['pending','successful','failed','cancelled','refunded'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="min-width:160px">
                <select name="purpose" class="form-select">
                    <option value="">All Purposes</option>
                    <?php foreach (['viewing_fee','registration_fee','verification_fee','agency_commission','featured_listing','other'] as $p): ?>
                    <option value="<?= $p ?>" <?= $filters['purpose']===$p?'selected':'' ?>><?= ucwords(str_replace('_',' ',$p)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="date" name="date_from" class="form-control" style="width:auto" value="<?= Security::e($filters['date_from']) ?>">
            <input type="date" name="date_to"   class="form-control" style="width:auto" value="<?= Security::e($filters['date_to']) ?>">
            <button class="btn btn-primary">Filter</button>
            <a href="<?= Url::base('admin/reports/export/revenue?date_from=' . date('Y-m-01') . '&date_to=' . date('Y-m-d')) ?>"
               class="btn btn-secondary">Export CSV</a>
        </form>
    </div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <p style="font-size:13px;color:var(--muted);margin:0"><?= $pager->info() ?></p>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Transaction Ref</th><th>User</th><th>Amount</th>
                    <th>Purpose</th><th>Gateway</th><th>Status</th><th>Date</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($payments)): ?>
            <tr><td colspan="8" style="text-align:center;padding:48px;color:var(--muted)">No payments found</td></tr>
            <?php else: ?>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><span class="code"><?= Security::e($p['transaction_ref']) ?></span></td>
                <td>
                    <div style="font-weight:600;font-size:13px"><?= Security::e($p['user_name']) ?></div>
                    <div style="font-size:11px;color:var(--muted)"><?= Security::e($p['email']) ?></div>
                </td>
                <td style="font-weight:800;font-size:15px"><?= Str::currency((float)$p['amount']) ?></td>
                <td style="font-size:12px"><?= ucwords(str_replace('_',' ',$p['purpose'])) ?></td>
                <td style="font-size:12px;color:var(--muted)"><?= Security::e($p['gateway'] ?? '—') ?></td>
                <td>
                    <span class="status-pill status-<?= Security::e($p['status']) ?>">
                        <?= ucfirst($p['status']) ?>
                    </span>
                </td>
                <td style="font-size:12px;color:var(--muted)">
                    <?= date('d M Y', strtotime($p['paid_at'] ?? $p['created_at'])) ?>
                </td>
                <td>
                    <a href="<?= Url::base('admin/payments/' . $p['id']) ?>"
                       class="btn btn-secondary btn-sm">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pager->lastPage > 1): ?>
    <div style="padding:16px 20px;border-top:1px solid var(--border)"><?= $pager->render() ?></div>
    <?php endif; ?>
</div>
