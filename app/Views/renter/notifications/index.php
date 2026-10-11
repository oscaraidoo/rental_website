<?php use App\Helpers\{Url, Security}; ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-0">Notifications
            <?php if ($unread > 0): ?><span class="badge bg-danger ms-1"><?= $unread ?></span><?php endif; ?>
        </h5>
        <p class="text-muted small mb-0"><?= (int)$total ?> total</p>
    </div>
    <?php if ($unread > 0): ?>
    <form method="POST" action="<?= Url::base('renter/notifications/read-all') ?>">
        <?= Security::csrfField() ?>
        <button class="btn btn-sm btn-outline-secondary">Mark all read</button>
    </form>
    <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
<div class="card border-0 shadow-sm text-center py-5">
    <div style="font-size:2.6rem;color:#8ba092;margin-bottom:8px">&#128276;</div>
    <h5 class="fw-bold">No notifications</h5>
    <p class="text-muted mb-0">You're all caught up!</p>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm p-0 overflow-hidden">
    <?php $items = $notifications; $dropdown = false; include ROOT_PATH . '/app/Views/partials/notification_list.php'; ?>
</div>
<?php if ($pager->lastPage > 1): ?><div class="mt-4"><?= $pager->render() ?></div><?php endif; ?>
<?php endif; ?>
