<?php use App\Helpers\{Url, Security}; ?>
<?php include __DIR__ . '/_layout.php'; ?>

<div class="prof-body">

    <!-- ── Change Password ────────────────────────────────── -->
    <div class="prof-card">
        <div class="prof-card-head">
            <div>
                <h3 class="prof-card-title">Change Password</h3>
                <p class="prof-card-sub">Use a strong password — at least 8 characters with an uppercase letter and a number</p>
            </div>
        </div>
        <div class="prof-card-body">
            <form method="POST" action="<?= Url::base('renter/profile/password') ?>" id="pwForm">
                <?= Security::csrfField() ?>
                <div class="prof-grid-3">
                    <div class="prof-field">
                        <label>Current password <span class="prof-req">*</span></label>
                        <div class="prof-pw-wrap">
                            <input type="password" name="current_password" id="pw0" required autocomplete="current-password" placeholder="••••••••">
                            <button type="button" class="prof-pw-eye" onclick="profTogglePw('pw0',this)" tabindex="-1">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="prof-field">
                        <label>New password <span class="prof-req">*</span></label>
                        <div class="prof-pw-wrap">
                            <input type="password" name="new_password" id="pw1" required minlength="8" autocomplete="new-password" placeholder="Min 8 characters" oninput="profPwStrength(this)">
                            <button type="button" class="prof-pw-eye" onclick="profTogglePw('pw1',this)" tabindex="-1">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <div class="prof-pw-bar"><div class="prof-pw-bar-fill" id="pwBar"></div></div>
                        <div class="prof-pw-hint" id="pwHint"></div>
                    </div>
                    <div class="prof-field">
                        <label>Confirm new password <span class="prof-req">*</span></label>
                        <div class="prof-pw-wrap">
                            <input type="password" name="confirm_password" id="pw2" required autocomplete="new-password" placeholder="Repeat password">
                            <button type="button" class="prof-pw-eye" onclick="profTogglePw('pw2',this)" tabindex="-1">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div style="margin-top:8px">
                    <button type="submit" class="btn btn-primary">Update password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Email verification ─────────────────────────────── -->
    <div class="prof-card">
        <div class="prof-card-head">
            <div>
                <h3 class="prof-card-title">Email Verification</h3>
                <p class="prof-card-sub">A verified email lets you recover your account and receive important alerts</p>
            </div>
        </div>
        <div class="prof-card-body">
            <div class="prof-security-row">
                <div class="prof-security-icon <?= !empty($renter['email_verified']) ? 'prof-security-icon--ok' : 'prof-security-icon--warn' ?>">
                    <?php if (!empty($renter['email_verified'])): ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <?php else: ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?php endif; ?>
                </div>
                <div style="flex:1">
                    <div class="prof-security-title"><?= Security::e($renter['email'] ?? '') ?></div>
                    <div class="prof-security-sub">
                        <?php if (!empty($renter['email_verified'])): ?>
                        Verified <?= !empty($renter['email_verified_at']) ? 'on ' . date('d M Y', strtotime($renter['email_verified_at'])) : '' ?>
                        <?php else: ?>
                        Not verified — check your inbox or request a new link
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (empty($renter['email_verified'])): ?>
                <form method="POST" action="<?= Url::base('renter/profile/resend-verification') ?>" style="margin:0">
                    <?= Security::csrfField() ?>
                    <button type="submit" class="btn btn-secondary btn-sm">Send verification email</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Security activity ──────────────────────────────── -->
    <div class="prof-card">
        <div class="prof-card-head">
            <div>
                <h3 class="prof-card-title">Recent Security Activity</h3>
                <p class="prof-card-sub">Last 20 security events on your account</p>
            </div>
        </div>
        <div class="prof-card-body" style="padding:0">
            <?php if (empty($activity)): ?>
            <div class="prof-empty">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.3"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <p>No security events recorded yet.</p>
            </div>
            <?php else: ?>
            <div class="prof-activity-list">
                <?php foreach ($activity as $ev):
                    $actionMap = [
                        'login'                => ['color' => '#16a34a', 'label' => 'Signed in'],
                        'login_failed'         => ['color' => '#dc2626', 'label' => 'Failed sign-in attempt'],
                        'logout'               => ['color' => '#64748b', 'label' => 'Signed out'],
                        'password_changed'     => ['color' => '#d97706', 'label' => 'Password changed'],
                        'profile_updated'      => ['color' => '#2563eb', 'label' => 'Profile updated'],
                        'profile_photo_changed'=> ['color' => '#2563eb', 'label' => 'Profile photo changed'],
                        'profile_photo_removed'=> ['color' => '#dc2626', 'label' => 'Profile photo removed'],
                        'account_deactivated'  => ['color' => '#dc2626', 'label' => 'Account deactivated'],
                        'settings_updated'     => ['color' => '#64748b', 'label' => 'Settings updated'],
                    ];
                    $info = $actionMap[$ev['action']] ?? ['color' => '#64748b', 'label' => ucwords(str_replace('_', ' ', $ev['action']))];
                ?>
                <div class="prof-activity-row">
                    <span class="prof-activity-dot" style="background:<?= $info['color'] ?>"></span>
                    <div class="prof-activity-body">
                        <span class="prof-activity-label"><?= $info['label'] ?></span>
                        <?php if (!empty($ev['ip_address'])): ?>
                        <span class="prof-activity-ip"><?= Security::e($ev['ip_address']) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="prof-activity-time"><?= date('d M Y H:i', strtotime($ev['created_at'])) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
function profTogglePw(id, btn) {
    var inp = document.getElementById(id);
    var hidden = inp.type === 'password';
    inp.type = hidden ? 'text' : 'password';
    btn.style.opacity = hidden ? '1' : '.5';
}
function profPwStrength(inp) {
    var v = inp.value, score = 0;
    if (v.length >= 8)  score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    var bar  = document.getElementById('pwBar');
    var hint = document.getElementById('pwHint');
    var map  = [
        [0,   'transparent', ''],
        [25,  '#dc2626',  'Weak'],
        [50,  '#d97706',  'Fair'],
        [75,  '#2563eb',  'Good'],
        [100, '#16a34a',  'Strong'],
    ];
    var s = map[Math.min(score, 4)];
    if (bar)  { bar.style.width = s[0] + '%'; bar.style.background = s[1]; }
    if (hint) { hint.textContent = s[2]; hint.style.color = s[1]; }
}
</script>
