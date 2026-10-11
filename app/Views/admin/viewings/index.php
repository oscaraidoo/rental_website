<?php use App\Helpers\{Url, Security, Str}; ?>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px">
    <?php
    $statItems = [
        ['label'=>'Total',          'value'=>$stats['total'],     'key'=>'primary'],
        ['label'=>'Awaiting Confirm','value'=>$stats['pending'],  'key'=>'warning'],
        ['label'=>'Confirmed',      'value'=>$stats['confirmed'], 'key'=>'info'],
        ['label'=>'Completed',      'value'=>$stats['completed'], 'key'=>'success'],
    ];
    foreach ($statItems as $s): ?>
    <div class="qs-card">
        <div class="qs-card-label"><?= $s['label'] ?></div>
        <div class="qs-card-value"><?= number_format($s['value']) ?></div>
        <div class="qs-card-accent"></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="table-card mb-4">
    <div style="padding:16px 20px">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
            <div style="min-width:160px">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php foreach (['pending_payment','paid','requested','confirmed','rescheduled','completed','cancelled','no_show'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>>
                        <?= ucwords(str_replace('_',' ',$s)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="date" name="date_from" class="form-control" style="width:auto" value="<?= Security::e($filters['date_from']) ?>">
            <input type="date" name="date_to"   class="form-control" style="width:auto" value="<?= Security::e($filters['date_to']) ?>">
            <button class="btn btn-primary">Filter</button>
            <a href="<?= Url::base('admin/viewings') ?>" class="btn btn-secondary">Clear</a>
        </form>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reference</th><th>Property</th><th>Renter</th>
                    <th>Date</th><th>Fee</th><th>Paid</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($viewings)): ?>
            <tr><td colspan="8" style="text-align:center;padding:48px;color:var(--muted)">No viewings found</td></tr>
            <?php else: ?>
            <?php foreach ($viewings as $v): ?>
            <tr>
                <td><span class="code"><?= Security::e($v['reference']) ?></span></td>
                <td style="font-size:13px;font-weight:600;max-width:160px">
                    <span style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= Security::e($v['property_title']) ?>
                    </span>
                </td>
                <td style="font-size:13px"><?= Security::e($v['renter_name']) ?></td>
                <td style="font-size:13px"><?= date('d M Y', strtotime($v['preferred_date'])) ?></td>
                <td style="font-weight:700"><?= Str::currency((float)$v['viewing_fee']) ?></td>
                <td style="text-align:center;font-size:13px">
                    <?= $v['fee_paid']
                        ? '<span style="color:#166534;font-weight:700">Yes</span>'
                        : '<span style="color:var(--muted)">No</span>' ?>
                </td>
                <td>
                    <span class="status-pill status-<?= Security::e($v['status']) ?>">
                        <?= ucwords(str_replace('_',' ',$v['status'])) ?>
                    </span>
                </td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:nowrap">
                        <a href="<?= Url::base('admin/viewings/' . $v['id']) ?>"
                           class="btn btn-secondary btn-sm">View</a>

                        <?php if ($v['status'] === 'paid'): ?>
                        <button class="btn btn-sm"
                                style="background:#f0fdf4;border:1.5px solid #bbf7d0;color:#166534"
                                data-bs-toggle="modal"
                                data-bs-target="#confirmModal<?= $v['id'] ?>">
                            Confirm
                        </button>
                        <?php endif; ?>

                        <?php if (in_array($v['status'], ['cancelled','no_show','pending_payment'])): ?>
                        <form method="POST"
                              action="<?= Url::base('admin/viewings/' . $v['id'] . '/delete') ?>">
                            <?= Security::csrfField() ?>
                            <button class="btn btn-sm"
                                    style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b"
                                    data-confirm="Delete viewing <?= Security::e($v['reference']) ?>?">
                                Delete
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>

            <!-- Confirm modal -->
            <?php if ($v['status'] === 'paid'): ?>
            <div class="modal fade" id="confirmModal<?= $v['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Viewing</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST"
                              action="<?= Url::base('admin/viewings/' . $v['id'] . '/confirm') ?>">
                            <?= Security::csrfField() ?>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Confirmed Date</label>
                                    <input type="date" name="confirmed_date" class="form-control"
                                           value="<?= Security::e($v['preferred_date']) ?>" required>
                                </div>
                                <div>
                                    <label class="form-label fw-medium">Confirmed Time</label>
                                    <input type="time" name="confirmed_time" class="form-control"
                                           value="<?= Security::e($v['preferred_time']) ?>" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Confirm Viewing</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pager->lastPage > 1): ?>
    <div style="padding:16px 20px;border-top:1px solid var(--border)"><?= $pager->render() ?></div>
    <?php endif; ?>
</div>
