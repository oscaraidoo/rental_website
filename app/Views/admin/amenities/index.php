<?php use App\Helpers\{Url, Security}; ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">

    <!-- Table -->
    <div class="table-card">
        <div class="table-card-head">
            <span class="table-card-title">All Amenities (<?= count($amenities) ?>)</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th><th>Slug</th><th>Category</th>
                        <th>Active</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($amenities as $a): ?>
                <tr>
                    <td style="font-weight:600;font-size:13px">
                        <?= Security::e($a['name']) ?>
                    </td>
                    <td><span class="code"><?= Security::e($a['slug']) ?></span></td>
                    <td style="font-size:12px;color:var(--muted)"><?= ucfirst($a['category'] ?? '') ?></td>
                    <td style="font-size:13px">
                        <?= $a['active']
                            ? '<span style="color:#166534;font-weight:700">Yes</span>'
                            : '<span style="color:var(--muted)">No</span>' ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-secondary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editAmenity<?= $a['id'] ?>">
                                Edit
                            </button>
                            <form method="POST"
                                  action="<?= Url::base('admin/amenities/' . $a['id'] . '/delete') ?>">
                                <?= Security::csrfField() ?>
                                <button class="btn btn-sm"
                                        style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b"
                                        data-confirm="Delete &quot;<?= Security::e($a['name']) ?>&quot;? It will be removed from all property listings.">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- Edit modal -->
                <div class="modal fade" id="editAmenity<?= $a['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Amenity</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST"
                                  action="<?= Url::base('admin/amenities/' . $a['id'] . '/update') ?>">
                                <?= Security::csrfField() ?>
                                <div class="modal-body">
                                    <div style="margin-bottom:14px">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control"
                                               value="<?= Security::e($a['name']) ?>" required>
                                    </div>
                                    <div style="margin-bottom:14px">
                                        <label class="form-label">Category</label>
                                        <select name="category" class="form-select">
                                            <?php foreach (['indoor','outdoor','utility','security','access'] as $cat): ?>
                                            <option value="<?= $cat ?>"
                                                <?= $a['category']===$cat?'selected':'' ?>>
                                                <?= ucfirst($cat) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <input type="checkbox" name="active" value="1"
                                               id="act_<?= $a['id'] ?>"
                                               <?= $a['active']?'checked':'' ?>
                                               style="width:16px;height:16px;cursor:pointer">
                                        <label for="act_<?= $a['id'] ?>"
                                               style="font-size:13px;color:var(--dark);cursor:pointer;margin:0">
                                            Active
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
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

    <!-- Add form -->
    <div class="table-card" style="position:sticky;top:calc(var(--nav-h,56px) + 24px)">
        <div class="table-card-head">
            <span class="table-card-title">Add Amenity</span>
        </div>
        <div style="padding:24px">
            <form method="POST" action="<?= Url::base('admin/amenities/store') ?>">
                <?= Security::csrfField() ?>
                <div style="margin-bottom:14px">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control"
                           placeholder="e.g. Ceiling Fan" required>
                </div>
                <div style="margin-bottom:14px">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <?php foreach (['indoor'=>'Indoor','outdoor'=>'Outdoor','utility'=>'Utility','security'=>'Security','access'=>'Access'] as $v => $l): ?>
                        <option value="<?= $v ?>"><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-bottom:20px">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="99">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">
                    Add Amenity
                </button>
            </form>
        </div>
    </div>

</div>
