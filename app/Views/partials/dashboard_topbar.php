<?php
use App\Helpers\{Url, Security};
use App\Services\NotificationService;
$userId   = (int)($_SESSION['user_id'] ?? 0);
$unread   = NotificationService::unreadCount($userId);
$recentNotifs = NotificationService::recent($userId, 6);
$userName = $_SESSION['user_name'] ?? 'User';
$role     = $_SESSION['user_role'] ?? 'renter';
$initial  = strtoupper(substr(trim($userName), 0, 1));
$firstName = explode(' ', trim($userName))[0];
$notifRoute = 'renter/notifications';
?>
<div class="app-topbar">

    <button id="sidebarToggle"
            style="background:none;border:1.5px solid var(--border);border-radius:var(--radius-sm);
                   padding:6px 10px;cursor:pointer;display:flex;flex-direction:column;
                   gap:4px;align-items:center;flex-shrink:0">
        <span style="display:block;width:16px;height:1.5px;background:var(--black)"></span>
        <span style="display:block;width:16px;height:1.5px;background:var(--black)"></span>
        <span style="display:block;width:12px;height:1.5px;background:var(--black)"></span>
    </button>

    <div style="flex:1"></div>

    <!-- Back to site -->
    <a href="<?= Url::base('properties') ?>"
       style="font-size:12px;font-weight:600;color:#235347;text-decoration:none;
              padding:6px 12px;border:1.5px solid var(--border);border-radius:var(--radius-sm)">
        Browse properties
    </a>

    <!-- Notifications bell dropdown -->
    <?php
    $items = $recentNotifs;
    $dropdown = true;
    $markAllUrl = Url::base('renter/notifications/read-all');
    include ROOT_PATH . '/app/Views/partials/notification_bell.php';
    ?>

    <!-- User pill -->
    <div class="dropdown" style="margin-left:4px">
        <button class="nav-avatar dropdown-toggle"
                data-bs-toggle="dropdown" style="--bs-btn-padding-x:0;border:none">
            <?= htmlspecialchars($initial) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" style="min-width:180px">
            <li class="px-3 py-2">
                <div style="font-size:13px;font-weight:700;color:var(--black)">
                    <?= Security::e($firstName) ?>
                </div>
                <div style="font-size:11px;color:var(--muted);text-transform:uppercase;
                             letter-spacing:.06em;font-weight:600;margin-top:2px">
                    <?= ucfirst($role) ?>
                </div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item"
                   href="<?= Url::base($role . '/profile') ?>">
                    Profile
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="<?= Url::base('auth/logout') ?>"
                   style="color:#dc2626">Sign out</a>
            </li>
        </ul>
    </div>

</div>
