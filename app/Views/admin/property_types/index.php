<?php use App\Helpers\{Url, Security}; ?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

    <div class="table-card">
        <div class="table-card-head">
            <span class="table-card-title">Property Types (<?= count($types) ?>)</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Order</th><th>Name</th><th>Slug</th><th>Active</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($types as $t): ?>
                <tr>
                    <td style="color:var(--muted);font-size:12px"><?= $t['sort_order'] ?></td>
                    <td style="font-weight:600;font-size:13px"><?= Security::e($t['name']) ?></td>
                    <td><span class="code"><?= Security::e($t['slug']) ?></span></td>
                    <td style="font-size:13px">
                        <?= $t['active']
                            ? '<span style="color:#166534;font-weight:700">Yes</span>'
                            : '<span style="color:var(--muted)">No</span>' ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-secondary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editType<?= $t['id'] ?>">
                                Edit
                            </button>
                            <form method="POST"
                                  action="<?= Url::base('admin/property-types/' . $t['id'] . '/delete') ?>">
                                <?= Security::csrfField() ?>
                                <button class="btn btn-sm"
                                        style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b"
                                        data-confirm="Delete &quot;<?= Security::e($t['name']) ?>&quot;? Only possible if no properties use it.">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- Edit modal -->
                <div class="modal fade" id="editType<?= $t['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Property Type</h5>
                                <button class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST"
                                  action="<?= Url::base('admin/property-types/' . $t['id'] . '/update') ?>">
                                <?= Security::csrfField() ?>
                                <div class="modal-body">
                                    <div style="margin-bottom:14px">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control"
                                               value="<?= Security::e($t['name']) ?>" required>
                                    </div>
                                    <div style="margin-bottom:14px">
                                        <label class="form-label">Sort Order</label>
                                        <input type="number" name="sort_order" class="form-control"
                                               value="<?= $t['sort_order'] ?>">
                                    </div>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <input type="checkbox" name="active" value="1"
                                               id="tact_<?= $t['id'] ?>"
                                               <?= $t['active']?'checked':'' ?>
                                               style="width:16px;height:16px;cursor:pointer">
                                        <label for="tact_<?= $t['id'] ?>"
                                               style="font-size:13px;cursor:pointer;margin:0">Active</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary" type="button"
                                            data-bs-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add -->
    <div class="table-card" style="position:sticky;top:calc(var(--nav-h,56px) + 24px)">
        <div class="table-card-head"><span class="table-card-title">Add Type</span></div>
        <div style="padding:24px">
            <form method="POST" action="<?= Url::base('admin/property-types/store') ?>">
                <?= Security::csrfField() ?>
                <div style="margin-bottom:14px">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control"
                           placeholder="e.g. Penthouse" required>
                </div>
                <div style="margin-bottom:20px">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="99">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">
                    Add Property Type
                </button>
            </form>
        </div>
    </div>

</div>
