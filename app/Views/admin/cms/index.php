<?php use App\Helpers\{Url, Security}; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold border-0 py-3">CMS Pages</div>
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead><tr><th>Slug</th><th>Title</th><th>Status</th><th>Last Updated</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($pages as $page): ?>
            <tr>
                <td><code><?= Security::e($page['slug']) ?></code></td>
                <td class="fw-medium"><?= Security::e($page['title']) ?></td>
                <td>
                    <span class="badge-status status-<?= $page['status'] === 'published' ? 'verified' : 'draft' ?>">
                        <?= ucfirst($page['status']) ?>
                    </span>
                </td>
                <td class="small text-muted"><?= date('d M Y', strtotime($page['updated_at'])) ?></td>
                <td>
                    <a href="<?= Url::base('admin/cms/' . $page['slug'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-pen me-1"></i>Edit
                    </a>
                    <a href="<?= Url::base('page/' . $page['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-external-link me-1"></i>View
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
