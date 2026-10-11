<?php use App\Helpers\{Url, Security, Str}; ?>

<!-- Filters -->
<div class="table-card mb-4">
    <div style="padding:16px 20px">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
            <div style="flex:1;min-width:200px">
                <input type="text" name="q" class="form-control"
                       placeholder="Search title or reference..."
                       value="<?= Security::e($filters['search']) ?>">
            </div>
            <div style="min-width:160px">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php foreach (['draft','submitted','under_review','inspection_required','verified','rejected','suspended','rented','expired'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>>
                        <?= ucwords(str_replace('_',' ',$s)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="min-width:160px">
                <select name="type_id" class="form-select">
                    <option value="">All Types</option>
                    <?php foreach ($types as $t): ?>
                    <option value="<?= $t['id'] ?>"
                        <?= (string)$filters['type_id']===(string)$t['id']?'selected':'' ?>>
                        <?= Security::e($t['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primary">Filter</button>
            <a href="<?= Url::base('admin/properties') ?>" class="btn btn-secondary">Clear</a>
        </form>
    </div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <p style="font-size:13px;color:var(--muted);margin:0"><?= $pager->info() ?></p>
    <div style="display:flex;gap:8px;align-items:center">
        <a href="<?= Url::base('admin/properties?status=submitted') ?>"
           class="btn btn-secondary btn-sm"
           style="<?= $filters['status']==='submitted' ? 'background:var(--black);color:#fff;border-color:var(--black)' : '' ?>">
            Pending
            <?php
            $pendingCount = (int)\App\Helpers\Database::fetchColumn(
                "SELECT COUNT(*) FROM properties WHERE status='submitted' AND deleted_at IS NULL"
            );
            if ($pendingCount): ?>
            <span style="background:var(--black);color:#fff;border-radius:20px;padding:1px 7px;font-size:10px;margin-left:4px;
                         <?= $filters['status']==='submitted' ? 'background:rgba(255,255,255,.2)' : '' ?>">
                <?= $pendingCount ?>
            </span>
            <?php endif; ?>
        </a>
        <a href="<?= Url::base('admin/properties/create') ?>" class="btn btn-primary btn-sm">
            + Add Property
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Property</th>
                    <th>Type</th>
                    <th>Landlord</th>
                    <th>City</th>
                    <th>Rent/mo</th>
                    <th>Status</th>
                    <th>Verified</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($properties)): ?>
            <tr>
                <td colspan="10" style="text-align:center;padding:48px;color:var(--muted)">
                    No properties found
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($properties as $i => $p): ?>
            <tr>
                <td>
                    <a href="<?= Url::base('admin/properties/' . $p['id']) ?>"
                       style="font-family:var(--mono);font-size:12px;color:var(--black);font-weight:600;text-decoration:none">
                        <?= Security::e($p['reference']) ?>
                    </a>
                </td>
                <td style="font-weight:600;max-width:200px">
                    <span style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px">
                        <?= Security::e($p['title']) ?>
                    </span>
                    <?php if (!empty($p['is_featured'])): ?>
                    <span style="font-size:10px;font-weight:700;color:#92400e;background:#fffbeb;
                                 border:1px solid #fde68a;padding:1px 6px;border-radius:3px">
                        Featured
                    </span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:var(--muted)"><?= Security::e($p['type_name']) ?></td>
                <td style="font-size:13px"><?= Security::e($p['landlord_name'] ?? '—') ?></td>
                <td style="font-size:12px;color:var(--muted)">
                    <?= Security::e($p['city_name'] ?? '—') ?>
                </td>
                <td style="font-weight:700"><?= Str::currency((float)$p['rent_monthly']) ?></td>
                <td>
                    <span class="status-pill status-<?= Security::e($p['status']) ?>">
                        <?= ucwords(str_replace('_', ' ', $p['status'])) ?>
                    </span>
                </td>
                <td style="text-align:center;font-size:13px">
                    <?= $p['verified']
                        ? '<span style="color:#166534;font-weight:700">Yes</span>'
                        : '<span style="color:var(--muted)">No</span>' ?>
                </td>
                <td style="font-size:12px;color:var(--muted)">
                    <?= date('d M Y', strtotime($p['created_at'])) ?>
                </td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:nowrap">
                        <a href="<?= Url::base('admin/properties/' . $p['id']) ?>"
                           class="btn btn-secondary btn-sm">View</a>

                        <a href="<?= Url::base('admin/properties/' . $p['id'] . '/edit') ?>"
                           class="btn btn-sm"
                           style="background:#f0f9f2;border:1.5px solid var(--border);color:var(--c-primary);font-weight:600">
                            Edit
                        </a>

                        <?php if (in_array($p['status'], ['submitted','under_review','inspection_required'])): ?>
                        <a href="<?= Url::base('admin/verifications/' . $p['id']) ?>"
                           class="btn btn-sm"
                           style="background:#fffbeb;border:1.5px solid #fde68a;color:#92400e">
                            Review
                        </a>
                        <?php endif; ?>

                        <?php if (in_array($p['status'], ['draft','rejected','suspended'])): ?>
                        <form method="POST"
                              action="<?= Url::base('admin/properties/' . $p['id'] . '/delete') ?>">
                            <?= Security::csrfField() ?>
                            <button class="btn btn-sm"
                                    style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b"
                                    data-confirm="Delete property <?= Security::e($p['reference']) ?>?">
                                Delete
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pager->lastPage > 1): ?>
    <div style="padding:16px 20px;border-top:1px solid var(--border)">
        <?= $pager->render() ?>
    </div>
    <?php endif; ?>
</div>
