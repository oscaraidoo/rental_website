<?php use App\Helpers\{Url, Security}; ?>

<?php if ($unread > 0): ?>
<div class="d-flex justify-content-end mb-2">
    <form method="POST" action="<?= Url::base('admin/notifications/read-all') ?>">
        <?= Security::csrfField() ?>
        <button class="btn btn-secondary btn-sm">Mark all read</button>
    </form>
</div>
<?php endif; ?>

<div class="table-card p-0" style="overflow:hidden">
    <?php
    $items = $notifications;
    $dropdown = false;
    if (empty($items)): ?>
    <div style="text-align:center;padding:56px 20px;color:var(--muted)">
        <div style="font-size:2.4rem;margin-bottom:8px">&#128276;</div>
        <div style="font-weight:700;color:var(--black)">No notifications</div>
        <div style="font-size:13px">You're all caught up!</div>
    </div>
    <?php else: include ROOT_PATH . '/app/Views/partials/notification_list.php'; endif; ?>
</div>

<?php if (!empty($notifications) && $pager->lastPage > 1): ?>
<div style="margin-top:16px"><?= $pager->render() ?></div>
<?php endif; ?>
