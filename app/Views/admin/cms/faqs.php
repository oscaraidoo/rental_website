<?php use App\Helpers\{Url, Security}; ?>
<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold border-0 py-3">All FAQs (<?= count($faqs) ?>)</div>
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead><tr><th>Category</th><th>Question</th><th>Order</th><th>Active</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($faqs as $f): ?>
                    <tr>
                        <td class="small"><?= ucfirst($f['category']) ?></td>
                        <td class="small"><?= Security::e(\App\Helpers\Str::truncate($f['question'], 60)) ?></td>
                        <td class="small text-center"><?= $f['sort_order'] ?></td>
                        <td><?= $f['active'] ? '<span class="text-success">Yes</span>' : '<span class="text-muted">No</span>' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editFaq<?= $f['id'] ?>">Edit</button>
                            <form method="POST" action="<?= Url::base('admin/faqs/' . $f['id'] . '/delete') ?>" class="d-inline">
                                <?= Security::csrfField() ?>
                                <button class="btn btn-sm btn-outline-danger" data-confirm="Delete this FAQ?"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <div class="modal fade" id="editFaq<?= $f['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg"><div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Edit FAQ</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                            <form method="POST" action="<?= Url::base('admin/faqs/' . $f['id'] . '/update') ?>">
                                <?= Security::csrfField() ?>
                                <div class="modal-body">
                                    <div class="mb-3"><label class="form-label fw-medium">Category</label>
                                        <select name="category" class="form-select">
                                            <?php foreach (['general','renter','landlord','payment','verification','viewing'] as $cat): ?>
                                            <option value="<?= $cat ?>" <?= $f['category'] === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3"><label class="form-label fw-medium">Question</label><input type="text" name="question" class="form-control" value="<?= Security::e($f['question']) ?>" required></div>
                                    <div class="mb-3"><label class="form-label fw-medium">Answer</label><textarea name="answer" class="form-control" rows="5" required><?= Security::e($f['answer']) ?></textarea></div>
                                    <div class="row g-3"><div class="col-md-6"><label class="form-label fw-medium">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= $f['sort_order'] ?>"></div>
                                    <div class="col-md-6 d-flex align-items-end"><div class="form-check"><input type="checkbox" name="active" class="form-check-input" value="1" <?= $f['active'] ? 'checked' : '' ?>><label class="form-check-label">Active</label></div></div></div>
                                </div>
                                <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
                            </form>
                        </div></div>
                    </div>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold border-0 py-3">Add FAQ</div>
            <div class="card-body">
                <form method="POST" action="<?= Url::base('admin/faqs/store') ?>">
                    <?= Security::csrfField() ?>
                    <div class="mb-3"><label class="form-label fw-medium">Category</label>
                        <select name="category" class="form-select">
                            <?php foreach (['general','renter','landlord','payment','verification','viewing'] as $cat): ?>
                            <option value="<?= $cat ?>"><?= ucfirst($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label fw-medium">Question</label><input type="text" name="question" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label fw-medium">Answer</label><textarea name="answer" class="form-control" rows="4" required></textarea></div>
                    <div class="mb-3"><label class="form-label fw-medium">Sort Order</label><input type="number" name="sort_order" class="form-control" value="99"></div>
                    <button type="submit" class="btn btn-primary w-100">Add FAQ</button>
                </form>
            </div>
        </div>
    </div>
</div>
