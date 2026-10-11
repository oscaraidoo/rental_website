<?php use App\Helpers\{Url, Security}; ?>
<div class="row g-4">
    <div class="col-12 col-lg-8">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold border-0 py-3 d-flex justify-content-between align-items-center">
                <span>Home Slides (<?= count($slides) ?>)</span>
                <a href="<?= Url::base('/') ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Preview home page</a>
            </div>
            <div class="table-responsive">
                <table class="table table-admin mb-0 align-middle">
                    <thead><tr>
                        <th style="width:96px">Media</th><th>Type</th><th>Label / Title</th>
                        <th class="text-center">Order</th><th class="text-center">Visible</th><th class="text-end">Actions</th>
                    </tr></thead>
                    <tbody>
                    <?php if (!$slides): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No slides yet — add one from the form on the right.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($slides as $s): ?>
                    <tr>
                        <td>
                            <?php if ($s['type'] === 'video'): ?>
                                <span style="display:inline-flex;width:84px;height:48px;border-radius:6px;background:#051F20;color:#DAF1DE;align-items:center;justify-content:center;font-size:18px">&#9654;</span>
                            <?php elseif (!empty($s['media_path'])): ?>
                                <img src="<?= Url::image($s['media_path']) ?>" alt="" style="width:84px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #d6ede0" onerror="this.style.opacity=.25">
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($s['type'] === 'video'): ?>
                                <span class="status-pill status-rented">Video</span>
                            <?php else: ?>
                                <span class="status-pill status-draft">Image</span>
                            <?php endif; ?>
                        </td>
                        <td class="small">
                            <strong><?= Security::e($s['label'] ?: '—') ?></strong><br>
                            <span class="text-muted"><?= Security::e(\App\Helpers\Str::truncate($s['title'], 48)) ?></span>
                        </td>
                        <td class="text-center small"><?= (int) $s['sort_order'] ?></td>
                        <td class="text-center">
                            <form method="POST" action="<?= Url::base('admin/cms/home/' . $s['id'] . '/toggle') ?>" class="d-inline">
                                <?= Security::csrfField() ?>
                                <button class="btn btn-sm btn-link text-decoration-none p-0" title="Toggle visibility">
                                    <?= $s['active'] ? '<span class="text-success">&#9679; Yes</span>' : '<span class="text-muted">&#9675; No</span>' ?>
                                </button>
                            </form>
                        </td>
                        <td class="text-end text-nowrap">
                            <form method="POST" action="<?= Url::base('admin/cms/home/' . $s['id'] . '/move/up') ?>" class="d-inline">
                                <?= Security::csrfField() ?>
                                <button class="btn btn-sm btn-outline-secondary" title="Move up">&uarr;</button>
                            </form>
                            <form method="POST" action="<?= Url::base('admin/cms/home/' . $s['id'] . '/move/down') ?>" class="d-inline">
                                <?= Security::csrfField() ?>
                                <button class="btn btn-sm btn-outline-secondary" title="Move down">&darr;</button>
                            </form>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSlide<?= $s['id'] ?>">Edit</button>
                            <form method="POST" action="<?= Url::base('admin/cms/home/' . $s['id'] . '/delete') ?>" class="d-inline">
                                <?= Security::csrfField() ?>
                                <button class="btn btn-sm btn-outline-danger" data-confirm="Delete this slide? This cannot be undone.">&times;</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php foreach ($slides as $s): ?>
        <div class="modal fade" id="editSlide<?= $s['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit slide #<?= $s['id'] ?></h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST" action="<?= Url::base('admin/cms/home/' . $s['id'] . '/update') ?>" enctype="multipart/form-data">
                    <?= Security::csrfField() ?>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Slide type</label>
                                <select name="type" class="form-select" data-type-select>
                                    <option value="image" <?= $s['type'] === 'image' ? 'selected' : '' ?>>Image</option>
                                    <option value="video" <?= $s['type'] === 'video' ? 'selected' : '' ?>>Video</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-medium">Replace media (optional)</label>
                                <input type="file" name="media_file" class="form-control" data-type-file accept="<?= $s['type'] === 'video' ? 'video/*' : 'image/*' ?>">
                                <div class="form-text">Leave empty to keep the current media.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">…or paste a direct media URL</label>
                                <input type="url" name="media_url" class="form-control" placeholder="https://example.com/hero.jpg or .mp4" value="">
                                <div class="form-text" data-type-hint><?= $s['type'] === 'video' ? 'Direct link to an .mp4 / .webm file.' : 'Direct link to a .jpg / .png / .webp image.' ?></div>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-medium">Label (small text)</label><input type="text" name="label" class="form-control" maxlength="150" value="<?= Security::e($s['label']) ?>"></div>
                            <div class="col-md-6"><label class="form-label fw-medium">Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int) $s['sort_order'] ?>"></div>
                            <div class="col-12"><label class="form-label fw-medium">Title</label><textarea name="title" class="form-control" rows="2" maxlength="255"><?= Security::e($s['title']) ?></textarea><div class="form-text">Line breaks become line breaks on screen.</div></div>
                            <div class="col-12"><label class="form-label fw-medium">Subtitle</label><textarea name="subtitle" class="form-control" rows="2" maxlength="500"><?= Security::e($s['subtitle']) ?></textarea></div>
                            <div class="col-md-6"><label class="form-label fw-medium">Button 1 text</label><input type="text" name="btn1_text" class="form-control" maxlength="80" value="<?= Security::e($s['btn1_text']) ?>"></div>
                            <div class="col-md-6"><label class="form-label fw-medium">Button 1 link</label><input type="text" name="btn1_url" class="form-control" placeholder="properties or https://…" value="<?= Security::e($s['btn1_url']) ?>"></div>
                            <div class="col-md-6"><label class="form-label fw-medium">Button 2 text (optional)</label><input type="text" name="btn2_text" class="form-control" maxlength="80" value="<?= Security::e($s['btn2_text']) ?>"></div>
                            <div class="col-md-6"><label class="form-label fw-medium">Button 2 link</label><input type="text" name="btn2_url" class="form-control" placeholder="how-it-works or https://…" value="<?= Security::e($s['btn2_url']) ?>"></div>
                            <div class="col-12"><div class="form-check"><input type="checkbox" name="active" class="form-check-input" value="1" id="eActive<?= $s['id'] ?>" <?= $s['active'] ? 'checked' : '' ?>><label class="form-check-label" for="eActive<?= $s['id'] ?>">Visible on home page</label></div></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save changes</button></div>
                </form>
            </div></div>
        </div>
        <?php endforeach; ?>

    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold border-0 py-3">Add slide</div>
            <div class="card-body">
                <form method="POST" action="<?= Url::base('admin/cms/home/store') ?>" enctype="multipart/form-data">
                    <?= Security::csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Slide type</label>
                        <select name="type" class="form-select" data-type-select>
                            <option value="image" selected>Image</option>
                            <option value="video">Video</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Upload media</label>
                        <input type="file" name="media_file" class="form-control" data-type-file accept="image/*">
                        <div class="form-text" id="addFileSize">Images up to 5 MB. Videos up to 100 MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">…or paste a media URL</label>
                        <input type="url" name="media_url" class="form-control" placeholder="https://example.com/hero.jpg or .mp4">
                        <div class="form-text" data-type-hint>Direct link to a .jpg / .png / .webp image.</div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-medium">Label (small text)</label><input type="text" name="label" class="form-control" maxlength="150" placeholder="e.g. For Landlords"></div>
                    <div class="mb-3"><label class="form-label fw-medium">Title</label><textarea name="title" class="form-control" rows="2" maxlength="255" placeholder="Big headline — line breaks are kept"></textarea></div>
                    <div class="mb-3"><label class="form-label fw-medium">Subtitle</label><textarea name="subtitle" class="form-control" rows="2" maxlength="500"></textarea></div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label fw-medium">Button 1 text</label><input type="text" name="btn1_text" class="form-control" maxlength="80" placeholder="Browse properties"></div>
                        <div class="col-6"><label class="form-label fw-medium">Button 1 link</label><input type="text" name="btn1_url" class="form-control" placeholder="properties or https://…"></div>
                        <div class="col-6"><label class="form-label fw-medium">Button 2 text</label><input type="text" name="btn2_text" class="form-control" maxlength="80"></div>
                        <div class="col-6"><label class="form-label fw-medium">Button 2 link</label><input type="text" name="btn2_url" class="form-control"></div>
                        <div class="col-6"><label class="form-label fw-medium">Order</label><input type="number" name="sort_order" class="form-control" value="<?= (count($slides) ? ((int) end($slides)['sort_order'] + 10) : 0) ?>"></div>
                        <div class="col-6 d-flex align-items-end"><div class="form-check"><input type="checkbox" name="active" class="form-check-input" value="1" id="addActive" checked><label class="form-check-label" for="addActive">Visible</label></div></div>
                    </div>
                    <button class="btn btn-primary w-100 mt-3">Add slide</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Sync file input accept attribute + URL hint with the chosen slide type
document.querySelectorAll('[data-type-select]').forEach(function (sel) {
    var form = sel.closest('form');
    var file = form.querySelector('[data-type-file]');
    var hint = form.querySelector('[data-type-hint]');
    function sync() {
        var isVideo = sel.value === 'video';
        if (file) file.accept = isVideo ? 'video/*' : 'image/*';
        if (hint) hint.textContent = isVideo
            ? 'Direct link to an .mp4 / .webm file.'
            : 'Direct link to a .jpg / .png / .webp image.';
    }
    sel.addEventListener('change', sync);
});
</script>
