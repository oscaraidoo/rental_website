<?php use App\Helpers\{Url, Security}; ?>
<?php /** @var array $rows @var array $filters @var array $summary @var array $types */ ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-0">Notification Log</h5>
        <p class="text-muted small mb-0">Outbound email &amp; SMS delivery history</p>
    </div>
</div>

<!-- Summary tiles -->
<div class="row g-3 mb-3">
    <?php foreach ([['Total', $summary['total'] ?? 0, 'secondary'], ['Sent', $summary['sent'] ?? 0, 'success'], ['Failed', $summary['failed'] ?? 0, 'danger'], ['Pending', $summary['pending'] ?? 0, 'warning']] as [$lbl, $val, $color]): ?>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="text-muted small text-uppercase" style="letter-spacing:.05em"><?= $lbl ?></div>
                <div class="fs-4 fw-bold text-<?= $color ?>"><?= (int) ($val ?? 0) ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<form method="GET" action="<?= Url::base('admin/notifications/log') ?>" class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
        <select name="channel" class="form-select form-select-sm w-auto">
            <option value="">All channels</option>
            <option value="email" <?= ($filters['channel'] ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
            <option value="sms" <?= ($filters['channel'] ?? '') === 'sms' ? 'selected' : '' ?>>SMS</option>
        </select>
        <select name="type" class="form-select form-select-sm w-auto">
            <option value="">All types</option>
            <?php foreach ($types as $type => $def): ?>
            <option value="<?= Security::e($type) ?>" <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>><?= Security::e($def['label']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="form-select form-select-sm w-auto">
            <option value="">All statuses</option>
            <?php foreach (['sent', 'failed', 'pending'] as $s): ?>
            <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-sm btn-primary">Filter</button>
        <?php if ($filters['channel'] || $filters['type'] || $filters['status']): ?>
        <a href="<?= Url::base('admin/notifications/log') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
        <?php endif; ?>
    </div>
</form>

<div class="table-card p-0" style="overflow:hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px">
            <thead>
                <tr class="small text-muted">
                    <th>When</th>
                    <th>Channel</th>
                    <th>Type</th>
                    <th>Recipient</th>
                    <th>Subject / Preview</th>
                    <th>Status</th>
                    <th>Provider</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="8" class="text-center text-muted py-5">No notifications match these filters.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td style="white-space:nowrap" title="<?= Security::e($r['created_at']) ?>"><?= Security::e(date('d M H:i', strtotime($r['created_at']))) ?></td>
                    <td><span class="badge bg-<?= $r['channel'] === 'email' ? 'info' : 'success' ?>"><?= Security::e($r['channel']) ?></span></td>
                    <td class="small"><?= Security::e($r['notification_type']) ?></td>
                    <td class="small"><?= Security::e($r['recipient']) ?></td>
                    <td class="small" style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= Security::e(($r['subject'] ? $r['subject'] . ' — ' : '') . $r['body_preview']) ?>">
                        <?= Security::e($r['subject'] ?: $r['body_preview']) ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= ['sent'=>'success','failed'=>'danger','pending'=>'warning'][$r['status']] ?? 'secondary' ?>"><?= Security::e($r['status']) ?></span>
                        <?php if (!empty($r['error_message'])): ?>
                        <div class="small text-danger" title="<?= Security::e($r['error_message']) ?>" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= Security::e($r['error_message']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="small">
                        <?= Security::e($r['provider'] ?: '—') ?>
                        <?php if ($r['provider_message_id']): ?><div class="text-muted" style="font-size:11px"><?= Security::e(mb_substr($r['provider_message_id'], 0, 20)) ?></div><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r['status'] === 'failed'): ?>
                        <form method="POST" action="<?= Url::base('admin/notifications/log/' . (int)$r['id'] . '/retry') ?>">
                            <?= Security::csrfField() ?>
                            <button class="btn btn-sm btn-outline-primary" title="Retry delivery">Retry</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($rows) && $pager->lastPage > 1): ?>
<div class="mt-3"><?= $pager->render() ?></div>
<?php endif; ?>
