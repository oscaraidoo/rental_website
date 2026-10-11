<?php use App\Helpers\{Url, Security}; ?>
<div class="row justify-content-center">
<div class="col-12 col-lg-9">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold border-0 py-3">New Blog Post</div>
    <div class="card-body">
        <form method="POST" action="<?= Url::base('admin/blog/store') ?>">
            <?= Security::csrfField() ?>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Category</label>
                    <input type="text" name="category" class="form-control" placeholder="e.g. Renter Tips">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Excerpt</label>
                    <textarea name="excerpt" class="form-control" rows="2" maxlength="500" placeholder="Short summary shown in listings..."></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Content <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control" rows="15" required placeholder="Full article content (HTML supported)..."></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2" maxlength="500"></textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-3">
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="fa-solid fa-save me-2"></i>Save Post
                </button>
                <a href="<?= Url::base('admin/blog') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
