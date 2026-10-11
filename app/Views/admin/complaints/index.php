<?php use App\Helpers\{Url, Security, Str}; ?>

<!-- Status tabs -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
    <?php foreach (['open'=>'Open','investigating'=>'Investigating','resolved'=>'Resolved','dismissed'=>'Dismissed'] as $s => $l): ?>
    <a href="?status=<?= $s ?>"
       class="btn btn-sm <?= $status === $s ? 'btn-primary' : 'btn-secondary' ?>">
        <?= $l ?>
        <?php if ($s === 'open' && $total > 0): ?>
        <span style="background:<?= $status==='open'?'rgba(255,255,255,.2)':'var(--black)' ?>;color:#fff;
                     border-radius:20px;padding:1px 7px;font-size:10px;margin-left:4px">
            <?= $total ?>
        </span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="table-card">
    <div class="table-card-head">
        <span class="table-card-title">
            <?= ucfirst($status) ?> Reports
            <span style="color:var(--muted);font-weight:400;font-size:12px;margin-left:6px">
                <?= number_format($total) ?> total
            </span>
        </span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Property</th><th>Reporter</th><th>Type</th>
                    <th>Status</th><th>Date</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($reports)): ?>
            <tr>
                <td colspan="6" style="text-align:center;padding:48px;color:var(--muted)">
                    No <?= $status ?> reports
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($reports as $r): ?>
            <tr>
                <td>
                    <div style="font-weight:600;font-size:13px">
                        <?= Security::e(Str::truncate($r['property_title'], 32)) ?>
                    </div>
                    <span class="code" style="font-size:11px"><?= Security::e($r['reference']) ?></span>
                </td>
                <td style="font-size:13px;color:var(--muted)">
                    <?= Security::e($r['reporter_name'] ?? 'Anonymous') ?>
                </td>
                <td style="font-size:12px">
                    <?= ucwords(str_replace('_',' ',$r['report_type'])) ?>
                </td>
                <td>
                    <span class="status-pill
                        <?= $r['status']==='resolved' ? 'status-verified'
                          : ($r['status']==='open'     ? 'status-pending'
                          : 'status-draft') ?>">
                        <?= ucfirst($r['status']) ?>
                    </span>
                </td>
                <td style="font-size:12px;color:var(--muted)">
                    <?= date('d M Y', strtotime($r['created_at'])) ?>
                </td>
                <td>
                    <a href="<?= Url::base('admin/complaints/' . $r['id']) ?>"
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
