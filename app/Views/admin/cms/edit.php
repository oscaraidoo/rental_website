<?php use App\Helpers\{Url, Security}; ?>

<form method="POST" action="<?= Url::base('admin/cms/' . $page['slug'] . '/update') ?>">
    <?= Security::csrfField() ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold border-0 py-3">
            Editing: <?= Security::e($page['title']) ?> <code class="small ms-2">/page/<?= Security::e($page['slug']) ?></code>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-medium">Page Title</label>
                <input type="text" name="title" class="form-control" value="<?= Security::e($page['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">Content <span class="text-muted fw-normal small">(HTML allowed)</span></label>
                <textarea name="content" class="form-control" id="pageContent" rows="20"><?= htmlspecialchars($page['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="<?= Security::e($page['meta_title'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Status</label>
                    <select name="status" class="form-select">
                        <option value="published" <?= $page['status']==='published'?'selected':'' ?>>Published</option>
                        <option value="draft"     <?= $page['status']==='draft'?'selected':'' ?>>Draft</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2" maxlength="500"><?= Security::e($page['meta_description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white border-0 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i>Save Page</button>
            <a href="<?= Url::base('admin/cms') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
</form>
