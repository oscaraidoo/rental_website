<?php use App\Helpers\Security; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="fw-bold mb-4"><?= Security::e($page['title']) ?></h1>
            <div class="cms-content" style="line-height:1.9;color:#374151">
                <?= $page['content'] ?? '<p class="text-muted">Content coming soon.</p>' ?>
            </div>
        </div>
    </div>
</div>
