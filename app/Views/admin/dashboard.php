<?php use App\Helpers\{Url, Security, Str}; ?>

<!-- ── 8 stat cards ─────────────────────────────────────── -->
<div class="admin-stat-grid">
    <?php
    $cards = [
        ['label'=>'Total Properties',    'value'=>number_format($stats['total_properties'])],
        ['label'=>'Verified / Live',      'value'=>number_format($stats['verified'])],
        ['label'=>'Pending Review',       'value'=>number_format($stats['pending'])],
        ['label'=>'Rented',               'value'=>number_format($stats['rented'])],
        ['label'=>'Property Owners',      'value'=>number_format($stats['total_landlords'])],
        ['label'=>'Renters',              'value'=>number_format($stats['total_renters'])],
        ['label'=>'Total Viewings',       'value'=>number_format($stats['viewings_total'])],
        ['label'=>'Revenue This Month',   'value'=>Str::currency($stats['revenue_month'])],
    ];
    foreach ($cards as $c): ?>
    <div class="qs-card">
        <div class="qs-card-label"><?= Security::e($c['label']) ?></div>
        <div class="qs-card-value"><?= Security::e($c['value']) ?></div>
        <div class="qs-card-accent"></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Charts ───────────────────────────────────────────── -->
<div class="admin-chart-grid-main" style="margin-bottom:28px">
    <div class="chart-card">
        <div class="chart-card-title">Revenue — Last 6 Months (GH&#8373;)</div>
        <canvas id="revenueChart"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-card-title">Properties by Status</div>
        <canvas id="statusChart"></canvas>
    </div>
</div>

<div class="admin-chart-grid-secondary" style="margin-bottom:28px">
    <div class="chart-card">
        <div class="chart-card-title">New Properties — Last 6 Months</div>
        <canvas id="propertiesChart"></canvas>
    </div>

    <!-- Recent payments -->
    <div class="table-card">
        <div class="table-card-head">
            <span class="table-card-title">Recent Payments</span>
            <a href="<?= Url::base('admin/payments') ?>" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div style="padding:0 20px">
            <?php if (empty($recentPayments)): ?>
            <div style="padding:32px 0;text-align:center;font-size:13px;color:var(--muted)">No payments yet</div>
            <?php else: ?>
            <?php foreach ($recentPayments as $pay): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:13px 0;border-bottom:1px solid var(--border)">
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--black);margin-bottom:2px">
                        <?= Security::e($pay['user_name']) ?>
                    </div>
                    <div style="font-size:11px;color:var(--muted)">
                        <?= ucwords(str_replace('_',' ',$pay['purpose'])) ?>
                    </div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:14px;font-weight:800;letter-spacing:-.02em">
                        <?= Str::currency((float)$pay['amount']) ?>
                    </div>
                    <span class="status-pill status-<?= Security::e($pay['status']) ?>"
                          style="font-size:10px">
                        <?= ucfirst($pay['status']) ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Pending review ────────────────────────────────────── -->
<div class="table-card mb-4">
    <div class="table-card-head">
        <span class="table-card-title">
            Pending Review
            <?php if (!empty($recentProperties)): ?>
            <span style="background:var(--black);color:#fff;border-radius:20px;
                         padding:2px 8px;font-size:10px;margin-left:6px;font-weight:700">
                <?= count($recentProperties) ?>
            </span>
            <?php endif; ?>
        </span>
        <a href="<?= Url::base('admin/properties?status=submitted') ?>"
           class="btn btn-secondary btn-sm">View all pending</a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reference</th><th>Property</th><th>Type</th>
                    <th>Landlord</th><th>City</th><th>Rent/mo</th>
                    <th>Submitted</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($recentProperties)): ?>
            <tr>
                <td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">
                    No properties pending review
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($recentProperties as $p): ?>
            <tr>
                <td><span class="code"><?= Security::e($p['reference']) ?></span></td>
                <td style="font-weight:600;font-size:13px;max-width:200px">
                    <span style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= Security::e($p['title']) ?>
                    </span>
                </td>
                <td style="font-size:12px;color:var(--muted)"><?= Security::e($p['type_name']) ?></td>
                <td style="font-size:13px"><?= Security::e($p['landlord_name'] ?? '—') ?></td>
                <td style="font-size:12px;color:var(--muted)"><?= Security::e($p['city_name'] ?? '—') ?></td>
                <td style="font-weight:700"><?= Str::currency((float)$p['rent_monthly']) ?></td>
                <td style="font-size:12px;color:var(--muted)">
                    <?= date('d M Y', strtotime($p['submitted_at'] ?? $p['created_at'])) ?>
                </td>
                <td>
                    <a href="<?= Url::base('admin/verifications/' . $p['id']) ?>"
                       class="btn btn-primary btn-sm">Review</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Recent viewings ───────────────────────────────────── -->
<div class="table-card">
    <div class="table-card-head">
        <span class="table-card-title">Recent Viewings</span>
        <a href="<?= Url::base('admin/viewings') ?>" class="btn btn-secondary btn-sm">View all</a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reference</th><th>Property</th><th>Renter</th>
                    <th>Date</th><th>Fee</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($recentViewings)): ?>
            <tr>
                <td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">
                    No viewings yet
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($recentViewings as $v): ?>
            <tr>
                <td><span class="code"><?= Security::e($v['reference']) ?></span></td>
                <td style="font-weight:600;font-size:13px;max-width:180px">
                    <span style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= Security::e($v['property_title']) ?>
                    </span>
                </td>
                <td style="font-size:13px;color:var(--muted)"><?= Security::e($v['renter_name']) ?></td>
                <td style="font-size:12px"><?= date('d M Y', strtotime($v['preferred_date'])) ?></td>
                <td style="font-weight:700"><?= Str::currency((float)$v['viewing_fee']) ?></td>
                <td>
                    <span class="status-pill status-<?= Security::e($v['status']) ?>">
                        <?= ucwords(str_replace('_',' ',$v['status'])) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
window.adminChartData = <?= json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
if (typeof initAdminCharts === 'function') initAdminCharts(window.adminChartData);
</script>
