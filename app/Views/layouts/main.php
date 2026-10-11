<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'RentalWebsite — See it. Understand it. Then decide. Ghana\'s most detailed property information platform.', ENT_QUOTES, 'UTF-8') ?>">
    <meta name="csrf-token" content="<?= \App\Helpers\Security::csrfToken() ?>">
    <?php
    // Canonical URL — full request path, without query string.
    // Strip the app's base path (e.g. /rental_website/public) from REQUEST_URI
    // so the canonical never doubles the base URL in subdirectory installs.
    $basePath = parse_url(\App\Helpers\Url::base(), PHP_URL_PATH) ?: '';
    $uriPath  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($basePath !== '' && str_starts_with($uriPath, $basePath)) {
        $uriPath = '/' . ltrim(substr($uriPath, strlen($basePath)), '/');
    }
    $canonicalUrl = $canonicalUrl ?? (rtrim(\App\Helpers\Url::base(), '/') . $uriPath);
    $robotsMeta   = $noindex ?? false ? 'noindex, nofollow' : 'index, follow';
    $ogImage      = $ogImage ?? null;
    ?>
    <meta name="robots" content="<?= $robotsMeta ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Open Graph / Twitter -->
    <meta property="og:site_name" content="RentalWebsite">
    <meta property="og:title" content="<?= htmlspecialchars(($title ?? 'Home') . ' — RentalWebsite', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription ?? 'Ghana\'s most detailed property information platform. See it. Understand it. Then decide.', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="<?= $ogType ?? 'website' ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($ogImage): ?>
    <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="<?= $ogImage ? 'summary_large_image' : 'summary' ?>">

    <!-- Resource hints: CDN + font origins (faster first paint) -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <title><?= htmlspecialchars(($title ?? 'Home') . ' — RentalWebsite', ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Design system -->
    <link rel="stylesheet" href="<?= \App\Helpers\Url::asset('css/app.css') ?>">

    <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($css, ENT_QUOTES) ?>">
    <?php endforeach; endif; ?>
</head>
<body class="has-nav <?= isset($heroPage) && $heroPage ? 'hero2-page' : '' ?>">

<?php include ROOT_PATH . '/app/Views/partials/navbar.php'; ?>

<!-- Flash messages -->
<?php if (\App\Helpers\Flash::has()): ?>
<div class="container" style="margin-top:16px">
    <?= \App\Helpers\Flash::render() ?>
</div>
<?php endif; ?>

<main>
    <?= $content ?>
</main>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>window.APP_BASE_URL = '<?= rtrim(\App\Helpers\Url::base(), '/') ?>';</script>
<script src="<?= \App\Helpers\Url::asset('js/app.js') ?>"></script>

<?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= htmlspecialchars($js, ENT_QUOTES) ?>"></script>
<?php endforeach; endif; ?>
<div id="toastContainer"></div>
</body>
</html>
