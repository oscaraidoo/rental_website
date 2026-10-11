<?php use App\Helpers\{Url, Security}; ?>

<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px;flex-wrap:wrap;gap:12px">
    <div>
        <h4 style="font-weight:800;margin-bottom:4px;letter-spacing:-.02em">
            Edit: <?= Security::e($p['title'] ?? '') ?>
        </h4>
        <p style="color:var(--muted);margin:0;font-size:14px">
            <span style="font-family:var(--mono);font-size:12px"><?= Security::e($p['reference'] ?? '') ?></span>
            &bull; Photos and verification are managed on the
            <a href="<?= Url::base('admin/properties/' . $propertyId) ?>" style="color:var(--c-primary)">property page</a>.
        </p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="<?= Url::base('admin/properties/' . $propertyId) ?>" class="btn btn-secondary btn-sm">
            View
        </a>
        <a href="<?= Url::base('admin/properties') ?>" class="btn btn-secondary btn-sm">
            &larr; All properties
        </a>
    </div>
</div>

<?php if (!empty($_SESSION['form_errors'])): ?>
<div style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:8px;padding:14px 18px;margin-bottom:20px">
    <strong style="font-size:13px;color:#991b1b">Please fix the following:</strong>
    <ul style="margin:6px 0 0 16px;padding:0;font-size:13px;color:#991b1b">
        <?php foreach ($_SESSION['form_errors'] as $err): ?>
        <li><?= Security::e($err) ?></li>
        <?php endforeach; unset($_SESSION['form_errors']); ?>
    </ul>
</div>
<?php endif; ?>

<?php include __DIR__ . '/_form.php'; ?>
