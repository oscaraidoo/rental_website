<?php
/**
 * Minimal layout for standalone auth pages (verify email, forgot/reset password).
 */
use App\Helpers\Url;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Helpers\Security::csrfToken() ?>">
    <title><?= htmlspecialchars(($title ?? 'Account') . ' — RentalWebsite', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= Url::asset('css/app.css') ?>">
</head>
<body style="background:#f8fbf9;min-height:100vh;display:flex;flex-direction:column">

<div style="border-bottom:1.5px solid #d6ede0;background:#ffffff;
            padding:0 32px;height:56px;display:flex;align-items:center;justify-content:space-between">
    <a href="<?= Url::base() ?>"
       style="font-size:16px;font-weight:800;color:#051F20;letter-spacing:-.03em;text-decoration:none">
        RentalWebsite
    </a>
    <a href="<?= Url::base('auth/login') ?>"
       style="font-size:13px;font-weight:600;color:#5a7a6e;text-decoration:none">
        Sign in
    </a>
</div>

<div style="flex:1;display:flex;align-items:center;justify-content:center;padding:40px 16px">
    <div style="width:100%;max-width:480px">
        <?= $content ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
