<?php use App\Helpers\{Url, Security, Str}; ?>

<!-- Status filter tabs -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
    <?php foreach ([
        'submitted'             => 'Submitted',
        'under_review'          => 'Under Review',
        'inspection_required'   => 'Inspection Required',
        'verified'              => 'Verified',
        'rejected'              => 'Rejected',
    ] as $s => $label): ?>
    <a href="?status=<?= $s ?>"
       class="btn btn-sm <?= ($filters['status'] ?? '') === $s ? 'btn-primary' : 'btn-secondary' ?>">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <div>
        <span style="font-size:15px;font-weight:700;color:var(--black)">
            Properties — <?= ucwords(str_replace('_',' ',$filters['status'] ?? 'all')) ?>
        </span>
        <span style="font-size:13px;color:var(--muted);margin-left:8px">
            <?= number_format($total) ?> found
        </span>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reference</th><th>Property</th><th>Type</th>
                    <th>Landlord</th><th>City</th><th>Rent/mo</th>
                    <th>Status</th><th>Submitted</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($properties)): ?>
            <tr>
                <td colspan="9" style="text-align:center;padding:48px;color:var(--muted)">
                    No properties in this status
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($properties as $p): ?>
            <tr>
                <td><span class="code"><?= Security::e($p['reference']) ?></span></td>
                <td style="font-weight:600;font-size:13px;max-width:180px">
                    <span style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= Security::e($p['title']) ?>
                    </span>
                </td>
                <td style="font-size:12px;color:var(--muted)"><?= Security::e($p['type_name']) ?></td>
                <td style="font-size:13px"><?= Security::e($p['landlord_name'] ?? '—') ?></td>
                <td style="font-size:12px;color:var(--muted)"><?= Security::e($p['city_name'] ?? '—') ?></td>
                <td style="font-weight:700"><?= Str::currency((float)$p['rent_monthly']) ?></td>
                <td>
                    <span class="status-pill status-<?= Security::e($p['status']) ?>">
                        <?= ucwords(str_replace('_', ' ', $p['status'])) ?>
                    </span>
                </td>
                <td style="font-size:12px;color:var(--muted)">
                    <?= $p['submitted_at'] ? date('d M Y', strtotime($p['submitted_at'])) : '—' ?>
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
    <?php if ($pager->lastPage > 1): ?>
    <div style="padding:16px 20px;border-top:1px solid var(--border)"><?= $pager->render() ?></div>
    <?php endif; ?>
</div>
