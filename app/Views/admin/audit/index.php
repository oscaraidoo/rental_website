<?php use App\Helpers\{Url, Security}; ?>

<!-- Filters -->
<div class="table-card mb-4">
    <div style="padding:16px 20px">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
            <div style="min-width:140px">
                <select name="module" class="form-select">
                    <option value="">All Modules</option>
                    <?php foreach ($modules as $m): ?>
                    <option value="<?= Security::e($m['module']) ?>"
                        <?= $filters['module']===$m['module']?'selected':'' ?>>
                        <?= ucfirst(Security::e($m['module'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="min-width:160px">
                <input type="text" name="action" class="form-control"
                       placeholder="Action e.g. login"
                       value="<?= Security::e($filters['action']) ?>">
            </div>
            <input type="date" name="date_from" class="form-control" style="width:auto" value="<?= Security::e($filters['date_from']) ?>">
            <input type="date" name="date_to"   class="form-control" style="width:auto" value="<?= Security::e($filters['date_to']) ?>">
            <button class="btn btn-primary">Filter</button>
            <a href="<?= Url::base('admin/audit-logs') ?>" class="btn btn-secondary">Clear</a>
        </form>
    </div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <p style="font-size:13px;color:var(--muted);margin:0"><?= $pager->info() ?></p>
    <span style="font-size:12px;color:var(--muted)"><?= number_format($total) ?> total entries</span>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Time</th><th>User</th><th>Action</th>
                    <th>Module</th><th>Description</th><th>IP</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($logs)): ?>
            <tr>
                <td colspan="6" style="text-align:center;padding:48px;color:var(--muted)">
                    No audit logs found
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td style="font-size:12px;color:var(--muted);white-space:nowrap">
                    <?= date('d M Y', strtotime($log['created_at'])) ?>
                    <br>
                    <span style="color:var(--muted)"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                </td>
                <td style="font-size:13px">
                    <?= Security::e($log['user_name'] ?? 'System') ?>
                </td>
                <td>
                    <span class="code"><?= Security::e($log['action']) ?></span>
                </td>
                <td>
                    <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;
                                 color:var(--muted);background:var(--light);padding:2px 8px;
                                 border-radius:3px;border:1px solid var(--border)">
                        <?= Security::e($log['module']) ?>
                    </span>
                </td>
                <td style="font-size:12px;color:var(--muted);max-width:280px">
                    <span style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= Security::e($log['description'] ?? '') ?>
                    </span>
                </td>
                <td style="font-family:var(--mono);font-size:11px;color:var(--muted)">
                    <?= Security::e($log['ip_address'] ?? '') ?>
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
