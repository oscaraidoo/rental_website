<?php use App\Helpers\{Url, Security}; ?>

<?php if (!$monitorEnabled): ?>
<div class="table-card" style="margin-bottom:20px;border-left:4px solid #f59e0b">
    <div style="padding:16px 20px;font-size:13px;color:var(--muted)">
        <strong style="color:var(--black)">Monitoring is currently off.</strong><br>
        It enables automatically in production (when <code>APP_DEBUG=false</code>), or force it on
        by adding <code>PERF_MONITOR=true</code> to your <code>.env</code> file.
    </div>
</div>
<?php endif; ?>

<!-- ── Summary cards ─────────────────────────────────────── -->
<div class="admin-stat-grid">
    <?php
    $cards = [
        ['label'=>'Requests (7d)',      'value'=>number_format((int)($summary['requests'] ?? 0))],
        ['label'=>'Avg Response',       'value'=>($summary['avg_ms'] ?? 0) . ' ms'],
        ['label'=>'p95 Response',       'value'=>$p95ms . ' ms'],
        ['label'=>'Worst Route',        'value'=>($summary['max_ms'] ?? 0) . ' ms'],
        ['label'=>'Avg DB Queries',     'value'=>($summary['avg_queries'] ?? 0)],
        ['label'=>'Slow Queries (7d)',  'value'=>number_format((int)($summary['slow_queries'] ?? 0))],
        ['label'=>'Errors (7d)',        'value'=>number_format((int)($summary['errors'] ?? 0))],
        ['label'=>'Peak Memory',        'value'=>'—'],
    ];
    foreach ($cards as $c): ?>
    <div class="qs-card">
        <div class="qs-card-label"><?= Security::e($c['label']) ?></div>
        <div class="qs-card-value"><?= Security::e((string)$c['value']) ?></div>
        <div class="qs-card-accent"></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Per-route breakdown ───────────────────────────────── -->
<div class="table-card" style="margin-top:24px">
    <div class="table-card-head">
        <span class="table-card-title">Per-Route Performance — Last 7 Days</span>
        <form method="POST" action="<?= Url::base('admin/performance/clear') ?>" style="margin:0"
              onsubmit="return confirm('Delete performance records older than 7 days?')">
            <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
            <button type="submit" class="btn btn-secondary btn-sm">Prune &gt; 7 days</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Route</th><th>Requests</th><th>Avg ms</th><th>Max ms</th>
                    <th>Avg Queries</th><th>Max Queries</th><th>Slow Q</th><th>Errors</th><th>Peak Mem</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($routes)): ?>
            <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--muted)">
                No performance data yet — monitoring collects one row per request.
            </td></tr>
            <?php else: ?>
            <?php foreach ($routes as $r): ?>
            <tr>
                <td><span class="code"><?= Security::e($r['route']) ?></span></td>
                <td><?= number_format((int)$r['requests']) ?></td>
                <td style="font-weight:600"><?= (float)$r['avg_ms'] ?> ms</td>
                <td style="<?= (int)$r['max_ms'] > 500 ? 'color:#dc2626;font-weight:700' : '' ?>">
                    <?= (int)$r['max_ms'] ?> ms
                </td>
                <td><?= (float)$r['avg_queries'] ?></td>
                <td><?= (int)$r['max_queries'] ?></td>
                <td style="<?= (int)$r['slow_queries'] > 0 ? 'color:#f59e0b;font-weight:700' : '' ?>">
                    <?= (int)$r['slow_queries'] ?>
                </td>
                <td style="<?= (int)$r['errors'] > 0 ? 'color:#dc2626;font-weight:700' : '' ?>">
                    <?= (int)$r['errors'] ?>
                </td>
                <td style="font-size:12px;color:var(--muted)"><?= number_format((int)$r['max_memory_kb']) ?> KB</td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Slow query counts ─────────────────────────────────── -->
<div class="table-card" style="margin-top:24px">
    <div class="table-card-head">
        <span class="table-card-title">Slow Queries (&gt; 100ms) — Last 7 Days</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Route</th><th>Requests w/ Slow Q</th><th>Slow Q Total</th><th>Worst Query Time</th></tr></thead>
            <tbody>
            <?php if (empty($slowQueries)): ?>
            <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--muted)">No slow queries recorded.</td></tr>
            <?php else: ?>
            <?php foreach ($slowQueries as $s): ?>
            <tr>
                <td><span class="code"><?= Security::e($s['route']) ?></span></td>
                <td><?= number_format((int)$s['requests']) ?></td>
                <td><?= (int)$s['slow_queries'] ?></td>
                <td><?= (int)$s['query_time_ms'] ?> ms</td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Errors ────────────────────────────────────────────── -->
<div class="table-card" style="margin-top:24px">
    <div class="table-card-head">
        <span class="table-card-title">Recent Errors — Last 7 Days</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>When</th><th>Route</th><th>Code</th><th>Type</th><th>Message</th></tr></thead>
            <tbody>
            <?php if (empty($errors)): ?>
            <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--muted)">No errors recorded.</td></tr>
            <?php else: ?>
            <?php foreach ($errors as $err): ?>
            <tr>
                <td style="font-size:12px;color:var(--muted)"><?= Security::e(date('d M H:i', strtotime($err['created_at']))) ?></td>
                <td><span class="code"><?= Security::e($err['route']) ?></span></td>
                <td><span class="status-pill status-<?= (int)$err['status_code'] >= 500 ? 'rejected' : 'pending' ?>">
                    <?= (int)$err['status_code'] ?></span></td>
                <td style="font-weight:600"><?= Security::e($err['error_type']) ?></td>
                <td style="font-size:12px;max-width:340px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                    title="<?= Security::e($err['error_message'] ?? '') ?>">
                    <?= Security::e(mb_substr($err['error_message'] ?? '', 0, 120)) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Daily trend ───────────────────────────────────────── -->
<div class="table-card" style="margin-top:24px;margin-bottom:32px">
    <div class="table-card-head">
        <span class="table-card-title">Daily Trend — Last 14 Days</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Day</th><th>Requests</th><th>Avg ms</th><th>Errors</th></tr></thead>
            <tbody>
            <?php if (empty($trend)): ?>
            <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--muted)">No data yet</td></tr>
            <?php else: ?>
            <?php foreach ($trend as $t): ?>
            <tr>
                <td style="font-weight:600"><?= Security::e($t['day']) ?></td>
                <td><?= number_format((int)$t['requests']) ?></td>
                <td><?= (float)$t['avg_ms'] ?> ms</td>
                <td style="<?= (int)$t['errors'] > 0 ? 'color:#dc2626;font-weight:700' : '' ?>"><?= (int)$t['errors'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
