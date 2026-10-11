<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Helpers\Security::csrfToken() ?>">
    <title><?= htmlspecialchars(($title ?? 'Dashboard') . ' — My Account · RentalWebsite', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= \App\Helpers\Url::asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= \App\Helpers\Url::asset('css/dashboard.css') ?>">
</head>
<body>

<div class="app-shell">
    <?php include ROOT_PATH . '/app/Views/partials/renter_sidebar.php'; ?>
    <div class="app-main" id="appMain">
        <?php include ROOT_PATH . '/app/Views/partials/dashboard_topbar.php'; ?>
        <div class="app-content">
            <?php if (!empty($title) && empty($noPageHead)): ?>
            <div class="page-head">
                <h1><?= \App\Helpers\Security::e($title) ?></h1>
            </div>
            <?php endif; ?>
            <?= \App\Helpers\Flash::render() ?>
            <?= $content ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>window.APP_BASE_URL = '<?= rtrim(\App\Helpers\Url::base(), '/') ?>';</script>
<script src="<?= \App\Helpers\Url::asset('js/app.js') ?>"></script>
<?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= htmlspecialchars($js, ENT_QUOTES) ?>"></script>
<?php endforeach; endif; ?>
<script>
var _sb  = document.querySelector('.app-sidebar');
var _tog = document.getElementById('sidebarToggle');

function openSidebar()  { _sb?.classList.add('open');    document.body.classList.add('sidebar-open'); }
function closeSidebar() { _sb?.classList.remove('open'); document.body.classList.remove('sidebar-open'); }

_tog?.addEventListener('click', function () {
    _sb?.classList.contains('open') ? closeSidebar() : openSidebar();
});
document.addEventListener('click', function (e) {
    if (window.innerWidth >= 992) return;
    if (!_sb?.classList.contains('open')) return;
    if (!_sb.contains(e.target) && !_tog?.contains(e.target)) closeSidebar();
});
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSidebar(); });
</script>
<div id="toastContainer"></div>
</body>
</html>
