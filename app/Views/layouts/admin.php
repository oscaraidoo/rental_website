<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Helpers\Security::csrfToken() ?>">
    <title><?= htmlspecialchars(($title ?? 'Dashboard') . ' — Admin · RentalWebsite', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= \App\Helpers\Url::asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= \App\Helpers\Url::asset('css/admin.css') ?>">
</head>
<body>

<div class="app-shell">

    <!-- ── Sidebar overlay (mobile) ─────────────────────── -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ── Sidebar ──────────────────────────────────────── -->
    <?php include ROOT_PATH . '/app/Views/partials/admin_sidebar.php'; ?>

    <!-- ── Main ─────────────────────────────────────────── -->
    <div class="app-main" id="appMain">

        <!-- Topbar -->
        <?php include ROOT_PATH . '/app/Views/partials/admin_topbar.php'; ?>

        <!-- Content -->
        <div class="app-content">

            <!-- Page heading -->
            <?php if (!empty($title)): ?>
            <div class="page-head">
                <div>
                    <?php if (!empty($breadcrumb)): ?>
                    <div class="app-breadcrumb" style="margin-bottom:8px">
                        <a href="<?= \App\Helpers\Url::base('admin/dashboard') ?>">Admin</a>
                        <?php foreach ($breadcrumb as $i => $bc): ?>
                        <span>/</span>
                        <?php if ($i === array_key_last($breadcrumb)): ?>
                        <span class="current"><?= \App\Helpers\Security::e($bc) ?></span>
                        <?php else: ?>
                        <a href="#"><?= \App\Helpers\Security::e($bc) ?></a>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <h1><?= \App\Helpers\Security::e($title) ?></h1>
                </div>
            </div>
            <?php endif; ?>

            <!-- Flash -->
            <?= \App\Helpers\Flash::render() ?>

            <!-- View -->
            <?= $content ?>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>window.APP_BASE_URL = '<?= rtrim(\App\Helpers\Url::base(), '/') ?>';</script>
<script src="<?= \App\Helpers\Url::asset('js/app.js') ?>"></script>
<script src="<?= \App\Helpers\Url::asset('js/admin.js') ?>"></script>
<?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= htmlspecialchars($js, ENT_QUOTES) ?>"></script>
<?php endforeach; endif; ?>

<script>
// Sidebar toggle
var _sb  = document.querySelector('.app-sidebar');
var _tog = document.getElementById('sidebarToggle');

function openSidebar() {
    _sb?.classList.add('open');
    document.body.classList.add('sidebar-open');
}
function closeSidebar() {
    _sb?.classList.remove('open');
    document.body.classList.remove('sidebar-open');
}

_tog?.addEventListener('click', function () {
    _sb?.classList.contains('open') ? closeSidebar() : openSidebar();
});

// Close on overlay click (the ::before pseudo-element covers inset:0)
document.addEventListener('click', function (e) {
    if (window.innerWidth >= 992) return;
    if (!_sb?.classList.contains('open')) return;
    // click was outside sidebar and outside toggle
    if (!_sb.contains(e.target) && !_tog?.contains(e.target)) {
        closeSidebar();
    }
});

// Close on Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeSidebar();
});
</script>
<div id="toastContainer"></div>
</body>
</html>
