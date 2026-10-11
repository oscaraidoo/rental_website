<?php use App\Helpers\{Url, Security, Str}; ?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Compare Properties</h2>
        <a href="<?= Url::base('properties') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Add More</a>
    </div>
    <?php if (empty($properties)): ?>
    <div class="card border-0 shadow-sm text-center py-5">
        <i class="fa-solid fa-code-compare text-muted mb-3" style="font-size:3rem"></i>
        <h5>No properties to compare</h5>
        <p class="text-muted">Browse properties and click "Add to Compare" to build your comparison.</p>
        <a href="<?= Url::base('properties') ?>" class="btn btn-primary px-4">Browse Properties</a>
    </div>
    <?php else: ?>
    <?php
    // Include the same comparison table from renter compare view
    include ROOT_PATH . '/app/Views/renter/compare/index.php';
    ?>
    <?php endif; ?>
</div>
