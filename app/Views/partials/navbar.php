<?php
use App\Helpers\{Url, Security};
$isLoggedIn = !empty($_SESSION['user_id']);
$role       = $_SESSION['user_role'] ?? '';
$userName   = $_SESSION['user_name'] ?? '';
$initial    = $userName ? strtoupper(substr(trim($userName), 0, 1)) : 'U';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$isActive    = fn(string $path): string => str_contains($currentPath, $path) ? 'active' : '';
?>
<nav class="site-nav">
    <div class="nav-inner">

        <!-- Brand -->
        <a href="<?= Url::base() ?>" class="nav-brand">
            Rental<span>Website</span>
        </a>

        <!-- Center links (hidden on mobile) -->
        <ul class="nav-links" style="margin:0;padding:0">
            <li><a href="<?= Url::base('properties') ?>" class="<?= $isActive('properties') ?>">Properties</a></li>
            <li><a href="<?= Url::base('how-it-works') ?>" class="<?= $isActive('how-it-works') ?>">How it works</a></li>
            <li><a href="<?= Url::base('for-renters') ?>" class="<?= $isActive('for-renters') ?>">For Renters</a></li>
            <li><a href="<?= Url::base('blog') ?>" class="<?= $isActive('blog') ?>">Blog</a></li>
        </ul>

        <!-- Right actions -->
        <div class="nav-actions">

            <?php if ($isLoggedIn): ?>

                <!-- Notification bell dropdown -->
                <?php
                $unread = \App\Services\NotificationService::unreadCount((int)$_SESSION['user_id']);
                $recentNotifs = \App\Services\NotificationService::recent((int)$_SESSION['user_id'], 6);
                $notifRoute = match ($role) {
                    'renter', 'landlord'    => 'renter/notifications',
                    'super_admin', 'admin'  => 'admin/notifications',
                    default                 => null
                };
                $items = $recentNotifs;
                $dropdown = true;
                $markAllUrl = $notifRoute ? Url::base(preg_replace('#/notifications$#', '/notifications/read-all', $notifRoute)) : null;
                if ($notifRoute) {
                    include ROOT_PATH . '/app/Views/partials/notification_bell.php';
                }
                ?>

                <!-- User dropdown -->
                <div class="dropdown">
                    <button class="nav-avatar dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"
                            style="--bs-btn-padding-x:0;border:none">
                        <?= htmlspecialchars($initial) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width:200px">
                        <li class="px-3 py-2">
                            <div style="font-size:13px;font-weight:700;color:#051F20"><?= Security::e($userName) ?></div>
                            <div style="font-size:12px;color:#5a7a6e"><?= Security::e($_SESSION['user_email'] ?? '') ?></div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if (in_array($role, ['super_admin','admin'])): ?>
                        <li><a class="dropdown-item" href="<?= Url::base('admin/dashboard') ?>">Admin Dashboard</a></li>
                        <?php else: ?>
                        <li><a class="dropdown-item" href="<?= Url::base('renter/dashboard') ?>">Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?= Url::base('renter/saved') ?>">Saved Properties</a></li>
                        <li><a class="dropdown-item" href="<?= Url::base('renter/compare') ?>">Compare</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= Url::base('auth/logout') ?>" style="color:#dc2626">Sign out</a></li>
                    </ul>
                </div>

            <?php else: ?>

                <a href="<?= Url::base('auth/login') ?>" class="btn btn-secondary btn-sm nav-auth-btn">Sign in</a>
                <a href="<?= Url::base('auth/register') ?>" class="btn btn-primary btn-sm nav-auth-btn">Get started</a>

            <?php endif; ?>

            <!-- Mobile hamburger -->
            <button class="nav-hamburger" id="navHamburger" aria-label="Open menu" type="button">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<!-- ── Mobile drawer ─────────────────────────────────────────
     Pure CSS drawer — no Bootstrap Offcanvas dependency.
     Toggle class 'open' on #mobileDrawer via JS below.
─────────────────────────────────────────────────────────── -->
<div class="mobile-drawer" id="mobileDrawer" aria-hidden="true">
    <div class="mobile-drawer-backdrop" id="mobileDrawerBackdrop"></div>
    <nav class="mobile-drawer-panel">
        <div class="mobile-drawer-header">
            <span class="mobile-drawer-brand">RentalWebsite</span>
            <button class="mobile-drawer-close" id="mobileDrawerClose" aria-label="Close menu" type="button">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="mobile-drawer-body">
            <!-- Main nav links -->
            <div class="mobile-drawer-section">
                <a href="<?= Url::base('properties') ?>"  class="mobile-drawer-link <?= $isActive('properties') ?>">Properties</a>
                <a href="<?= Url::base('how-it-works') ?>" class="mobile-drawer-link <?= $isActive('how-it-works') ?>">How it works</a>
                <a href="<?= Url::base('for-renters') ?>"  class="mobile-drawer-link <?= $isActive('for-renters') ?>">For Renters</a>
                <a href="<?= Url::base('for-landlords') ?>" class="mobile-drawer-link <?= $isActive('for-landlords') ?>">For Landlords</a>
                <a href="<?= Url::base('blog') ?>"         class="mobile-drawer-link <?= $isActive('blog') ?>">Blog</a>
                <a href="<?= Url::base('faqs') ?>"         class="mobile-drawer-link <?= $isActive('faqs') ?>">FAQs</a>
                <a href="<?= Url::base('contact') ?>"      class="mobile-drawer-link <?= $isActive('contact') ?>">Contact</a>
            </div>

            <div class="mobile-drawer-divider"></div>

            <!-- Auth section -->
            <?php if ($isLoggedIn): ?>
            <div class="mobile-drawer-section">
                <?php if (in_array($role, ['super_admin','admin'])): ?>
                <a href="<?= Url::base('admin/dashboard') ?>" class="mobile-drawer-link">Admin Panel</a>
                <?php else: ?>
                <a href="<?= Url::base('renter/dashboard') ?>"    class="mobile-drawer-link">Dashboard</a>
                <a href="<?= Url::base('renter/saved') ?>"        class="mobile-drawer-link">Saved Properties</a>
                <a href="<?= Url::base('renter/compare') ?>"      class="mobile-drawer-link">Compare</a>
                <a href="<?= Url::base('renter/notifications') ?>" class="mobile-drawer-link">Notifications<?php if ($unread > 0): ?> <span class="mobile-drawer-badge"><?= $unread ?></span><?php endif; ?></a>
                <?php endif; ?>
                <a href="<?= Url::base('auth/logout') ?>" class="mobile-drawer-link mobile-drawer-link--danger">Sign out</a>
            </div>
            <?php else: ?>
            <div class="mobile-drawer-auth">
                <a href="<?= Url::base('auth/login') ?>"    class="btn btn-secondary w-100">Sign in</a>
                <a href="<?= Url::base('auth/register') ?>" class="btn btn-primary  w-100">Get started</a>
            </div>
            <?php endif; ?>
        </div>
    </nav>
</div>

<script>
(function () {
    var hamburger = document.getElementById('navHamburger');
    var drawer    = document.getElementById('mobileDrawer');
    var backdrop  = document.getElementById('mobileDrawerBackdrop');
    var closeBtn  = document.getElementById('mobileDrawerClose');

    function openDrawer() {
        drawer.classList.add('open');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer() {
        drawer.classList.remove('open');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (hamburger) hamburger.addEventListener('click', openDrawer);
    if (closeBtn)  closeBtn.addEventListener('click',  closeDrawer);
    if (backdrop)  backdrop.addEventListener('click',  closeDrawer);

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDrawer();
    });
})();
</script>
