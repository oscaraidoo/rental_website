<?php use App\Helpers\{Url, Security}; ?>

<div class="table-card">
    <div class="table-card-head">
        <span class="table-card-title">Landlord Agreements</span>
        <span style="font-size:13px;color:var(--muted)"><?= $pager->info() ?></span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reference</th><th>Landlord</th><th>Type</th>
                    <th>Status</th><th>Signed</th><th>Created</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($agreements)): ?>
            <tr>
                <td colspan="7" style="text-align:center;padding:48px;color:var(--muted)">
                    No agreements yet
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($agreements as $a): ?>
            <tr>
                <td><span class="code"><?= Security::e($a['agreement_ref']) ?></span></td>
                <td>
                    <div style="font-weight:600;font-size:13px">
                        <?= Security::e($a['landlord_name']) ?>
                    </div>
                    <div style="font-size:11px;color:var(--muted)">
                        <?= Security::e($a['email']) ?>
                    </div>
                </td>
                <td style="font-size:12px;color:var(--muted)">
                    <?= ucwords(str_replace('_',' ',$a['agreement_type'])) ?>
                </td>
                <td>
                    <span class="status-pill
                        <?= $a['status']==='signed'  ? 'status-verified'
                          : ($a['status']==='sent'   ? 'status-pending'
                          : 'status-draft') ?>">
                        <?= ucfirst($a['status']) ?>
                    </span>
                </td>
                <td style="font-size:12px;color:var(--muted)">
                    <?= $a['signed_at'] ? date('d M Y', strtotime($a['signed_at'])) : '—' ?>
                </td>
                <td style="font-size:12px;color:var(--muted)">
                    <?= date('d M Y', strtotime($a['created_at'])) ?>
                </td>
                <td>
                    <a href="<?= Url::base('admin/agreements/' . $a['id']) ?>"
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
