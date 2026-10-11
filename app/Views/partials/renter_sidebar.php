<?php
use App\Helpers\Url;
$p      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$active = fn(string $seg): string => str_contains($p, $seg) ? 'active' : '';

$savedCount   = (int)\App\Helpers\Database::fetchColumn("SELECT COUNT(*) FROM saved_properties WHERE user_id = ?", [$_SESSION['user_id'] ?? 0]) ?: 0;
$compareCount = (int)\App\Helpers\Database::fetchColumn("SELECT COUNT(*) FROM property_comparisons WHERE user_id = ?", [$_SESSION['user_id'] ?? 0]) ?: 0;
$upcomingCount = (int)\App\Helpers\Database::fetchColumn("SELECT COUNT(*) FROM viewing_requests WHERE renter_id = ? AND status = 'confirmed' AND confirmed_date >= CURDATE()", [$_SESSION['user_id'] ?? 0]) ?: 0;
?>
<nav class="app-sidebar" id="appSidebar">
    <div class="sidebar-logo">
        <a href="<?= Url::base() ?>" style="color:inherit;text-decoration:none">RentalWebsite</a>
    </div>
    <div class="sidebar-nav">
        <a href="<?= Url::base('renter/dashboard') ?>" class="sidebar-link <?= $active('renter/dashboard') ?>">Dashboard</a>

        <div class="sidebar-section">Properties</div>
        <a href="<?= Url::base('properties') ?>" class="sidebar-link">Search Properties</a>
        <a href="<?= Url::base('renter/saved') ?>" class="sidebar-link <?= $active('renter/saved') ?>">
            Saved
            <?php if ($savedCount): ?>
            <span class="sidebar-badge"><?= $savedCount ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= Url::base('renter/compare') ?>" class="sidebar-link <?= $active('renter/compare') ?>">
            Compare
            <?php if ($compareCount): ?>
            <span class="sidebar-badge"><?= $compareCount ?></span>
            <?php endif; ?>
        </a>

        <div class="sidebar-section">Viewings</div>
        <a href="<?= Url::base('renter/viewings') ?>" class="sidebar-link <?= $active('renter/viewings') ?>">
            My Viewings
            <?php if ($upcomingCount): ?>
            <span class="sidebar-badge"><?= $upcomingCount ?></span>
            <?php endif; ?>
        </a>

        <div class="sidebar-section">Account</div>
        <a href="<?= Url::base('renter/payments') ?>" class="sidebar-link <?= $active('renter/payments') ?>">Payments</a>
        <a href="<?= Url::base('renter/notifications') ?>" class="sidebar-link <?= $active('renter/notifications') ?>">Notifications</a>
        <a href="<?= Url::base('renter/notifications/preferences') ?>" class="sidebar-link <?= $active('renter/notifications/preferences') ?>" style="font-size:12.5px;padding-left:22px">&#8635; Notification Settings</a>
        <a href="<?= Url::base('renter/profile') ?>" class="sidebar-link <?= $active('renter/profile') ?>">Profile</a>

        <div style="height:1px;background:var(--border);margin:16px 0"></div>
        <a href="<?= Url::base('auth/logout') ?>" class="sidebar-link" style="color:#dc2626">Sign out</a>
    </div>
</nav>
