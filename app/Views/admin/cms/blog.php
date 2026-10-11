<?php use App\Helpers\{Url, Security, Str}; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Blog Posts</h5>
    <a href="<?= Url::base('admin/blog/create') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>New Post</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Views</th><th>Published</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($posts)): ?>
            <tr><td colspan="7" class="text-center text-muted py-5">No blog posts yet.</td></tr>
            <?php else: ?>
            <?php foreach ($posts as $p): ?>
            <tr>
                <td class="fw-medium small"><?= Security::e(Str::truncate($p['title'], 45)) ?></td>
                <td class="small text-muted"><?= Security::e($p['category'] ?? '—') ?></td>
                <td class="small"><?= Security::e($p['author_name']) ?></td>
                <td><span class="badge-status status-<?= $p['status'] === 'published' ? 'verified' : 'draft' ?>"><?= ucfirst($p['status']) ?></span></td>
                <td class="small text-center"><?= number_format($p['view_count']) ?></td>
                <td class="small text-muted"><?= $p['published_at'] ? date('d M Y', strtotime($p['published_at'])) : '—' ?></td>
                <td>
                    <a href="<?= Url::base('admin/blog/' . $p['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <a href="<?= Url::base('blog/' . $p['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                    <form method="POST" action="<?= Url::base('admin/blog/' . $p['id'] . '/delete') ?>" class="d-inline">
                        <?= Security::csrfField() ?>
                        <button class="btn btn-sm btn-outline-danger"
                                data-confirm="Delete this blog post? This cannot be undone.">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
