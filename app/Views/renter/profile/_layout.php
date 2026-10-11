<?php
/**
 * Profile module layout partial.
 * Include at the top of every profile tab view.
 * Expects: $renter, $tab, $completion (optional)
 */
use App\Helpers\{Url, Security};

$avatar   = $renter['avatar'] ?? null;
$gender   = $renter['gender'] ?? '';
$userName = trim(($renter['first_name'] ?? '') . ' ' . ($renter['last_name'] ?? ''));
$email    = $renter['email'] ?? '';
$status   = $renter['user_status'] ?? $renter['status'] ?? 'active';
$verified = !empty($renter['email_verified']);
$userId   = $renter['user_id'] ?? $_SESSION['user_id'] ?? 0;

// Completion — compute here directly so we don't need a use import
if (!isset($completion)) {
    $completion = \App\Controllers\Renter\ProfileController::completionScore($renter);
}
$pct = $completion['percent'];
$genderEmoji = '';

$pctColor = $pct >= 80 ? '#16a34a' : ($pct >= 50 ? '#d97706' : '#dc2626');

$statusMap = [
    'active'               => ['label' => 'Active',            'class' => 'prof-status--active'],
    'inactive'             => ['label' => 'Inactive',          'class' => 'prof-status--inactive'],
    'suspended'            => ['label' => 'Suspended',         'class' => 'prof-status--suspended'],
    'pending_verification' => ['label' => 'Pending Verify',    'class' => 'prof-status--pending'],
];
$statusInfo = $statusMap[$status] ?? ['label' => ucfirst($status), 'class' => 'prof-status--inactive'];

$tabs = [
    'profile'  => ['label' => 'Profile',      'url' => 'renter/profile',
        'icon' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
    'security' => ['label' => 'Security',     'url' => 'renter/profile/security',
        'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
    'settings' => ['label' => 'Settings',     'url' => 'renter/profile/settings',
        'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
    'account'  => ['label' => 'Account',      'url' => 'renter/profile/account',
        'icon' => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>'],
];
?>
<!-- ── Profile Hero ─────────────────────────────────────────── -->
<div class="prof-hero">
    <div class="prof-hero-bg"></div>
    <div class="prof-hero-content">

        <!-- Avatar -->
        <div class="prof-avatar-zone" id="profAvatarZone">
            <div class="prof-avatar-ring">
                <?php if ($avatar): ?>
                <img src="<?= Url::image($avatar) ?>" alt="<?= Security::e($userName) ?>" class="prof-avatar-img" id="profAvatarImg">
                <?php else: ?>
                <div class="prof-avatar-initials" id="profAvatarImg"><?= strtoupper(substr($userName, 0, 1)) ?: 'U' ?></div>
                <?php endif; ?>
                <label for="profPhotoInput" class="prof-avatar-cam" title="Change photo">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                </label>
            </div>
            <input type="file" id="profPhotoInput" accept="image/jpeg,image/png,image/webp" style="display:none">
        </div>

        <!-- Identity -->
        <div class="prof-hero-info">
            <div class="prof-hero-name">
                <?= Security::e($userName) ?>
                <?php if ($verified): ?>
                <span class="prof-verified-badge" title="Email verified">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="#16a34a" stroke="#fff" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                <?php endif; ?>
            </div>
            <div class="prof-hero-email"><?= Security::e($email) ?></div>
            <div class="prof-hero-meta">
                <span class="prof-status <?= $statusInfo['class'] ?>"><?= $statusInfo['label'] ?></span>
                <span class="prof-hero-role">Renter</span>
                <span class="prof-hero-id">ID #<?= str_pad((string)$userId, 5, '0', STR_PAD_LEFT) ?></span>
            </div>
        </div>

        <!-- Completion ring -->
        <div class="prof-completion-widget">
            <div class="prof-completion-ring">
                <svg width="72" height="72" viewBox="0 0 72 72">
                    <circle cx="36" cy="36" r="30" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="5"/>
                    <circle cx="36" cy="36" r="30" fill="none"
                            stroke="<?= $pctColor ?>" stroke-width="5"
                            stroke-dasharray="<?= round(2 * M_PI * 30, 1) ?>"
                            stroke-dashoffset="<?= round(2 * M_PI * 30 * (1 - $pct / 100), 1) ?>"
                            stroke-linecap="round"
                            transform="rotate(-90 36 36)"/>
                    <text x="36" y="40" text-anchor="middle" font-size="14" font-weight="800"
                          fill="#fff" font-family="Inter,system-ui,sans-serif"><?= $pct ?>%</text>
                </svg>
            </div>
            <div class="prof-completion-label">Profile complete</div>
            <?php if (!empty($completion['missing'])): ?>
            <div class="prof-completion-tip">Add: <?= Security::e($completion['missing'][0]) ?></div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- ── Tab nav ──────────────────────────────────────────────── -->
<div class="prof-tabs-wrap">
    <nav class="prof-tabs" role="tablist">
        <?php foreach ($tabs as $key => $t): ?>
        <a href="<?= Url::base($t['url']) ?>"
           class="prof-tab <?= $tab === $key ? 'active' : '' ?>"
           role="tab" aria-selected="<?= $tab === $key ? 'true' : 'false' ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <?= $t['icon'] ?>
            </svg>
            <span><?= $t['label'] ?></span>
        </a>
        <?php endforeach; ?>
    </nav>
</div>

<!-- Hidden photo-upload form (submits via JS) -->
<form id="profPhotoForm" method="POST"
      action="<?= Url::base('renter/profile/photo') ?>"
      enctype="multipart/form-data" style="display:none">
    <?= Security::csrfField() ?>
    <input type="file" name="avatar" id="profPhotoFormFile" accept="image/jpeg,image/png,image/webp">
</form>
