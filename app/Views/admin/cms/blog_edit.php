<?php use App\Helpers\{Url, Security}; ?>
<div class="row justify-content-center">
<div class="col-12 col-lg-9">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold border-0 py-3">Edit Blog Post</div>
    <div class="card-body">
        <form method="POST" action="<?= Url::base('admin/blog/' . $post['id'] . '/update') ?>">
            <?= Security::csrfField() ?>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required maxlength="255"
                           value="<?= Security::e($post['title']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Category</label>
                    <input type="text" name="category" class="form-control"
                           value="<?= Security::e($post['category'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Excerpt</label>
                    <textarea name="excerpt" class="form-control" rows="2" maxlength="500"><?= Security::e($post['excerpt'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Content <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control" rows="15" required><?= htmlspecialchars($post['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" maxlength="255"
                           value="<?= Security::e($post['meta_title'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft"     <?= $post['status'] === 'draft'     ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="archived"  <?= $post['status'] === 'archived'  ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2" maxlength="500"><?= Security::e($post['meta_description'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-3">
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="fa-solid fa-save me-2"></i>Update Post
                </button>
                <a href="<?= Url::base('blog/' . $post['slug']) ?>" target="_blank"
                   class="btn btn-outline-secondary">
                    <i class="fa-solid fa-external-link me-2"></i>View Live
                </a>
                <a href="<?= Url::base('admin/blog') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
