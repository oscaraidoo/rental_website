<?php use App\Helpers\{Url, Security}; ?>
<?php include __DIR__ . '/_layout.php'; ?>

<?php
$p = $prefs ?? [];
$on = fn(string $key, bool $default = true): bool => isset($p[$key]) ? (bool)$p[$key] : $default;
?>

<div class="prof-body">
    <form method="POST" action="<?= Url::base('renter/profile/settings') ?>" id="settingsForm">
        <?= Security::csrfField() ?>

        <!-- ── Notification Channels ──────────────────────── -->
        <div class="prof-card">
            <div class="prof-card-head">
                <div>
                    <h3 class="prof-card-title">Notification Preferences</h3>
                    <p class="prof-card-sub">Choose how and when you hear from us</p>
                </div>
                <button type="submit" class="prof-save-btn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Save
                </button>
            </div>
            <div class="prof-card-body">

                <?php
                $toggles = [
                    ['key' => 'email_notifications', 'label' => 'Email notifications',
                     'sub' => 'Viewing confirmations, payment receipts and account alerts', 'default' => true],
                    ['key' => 'sms_notifications',   'label' => 'SMS notifications',
                     'sub' => 'Urgent alerts and viewing reminders via text message', 'default' => false],
                    ['key' => 'inapp_notifications',  'label' => 'In-app notifications',
                     'sub' => 'Bell icon alerts inside your dashboard', 'default' => true],
                    ['key' => 'viewing_reminders',    'label' => 'Viewing reminders',
                     'sub' => 'Reminders before your scheduled property viewings', 'default' => true],
                    ['key' => 'property_alerts',      'label' => 'Property alerts',
                     'sub' => 'New listings that match your saved search criteria', 'default' => true],
                    ['key' => 'marketing_emails',     'label' => 'Marketing emails',
                     'sub' => 'Promotions, tips and platform news — you can opt out anytime', 'default' => false],
                ];
                foreach ($toggles as $t): ?>
                <div class="prof-toggle-row">
                    <div>
                        <div class="prof-toggle-row-title"><?= $t['label'] ?></div>
                        <div class="prof-toggle-row-sub"><?= $t['sub'] ?></div>
                    </div>
                    <label class="prof-switch">
                        <input type="checkbox" name="<?= $t['key'] ?>" value="1"
                               <?= $on($t['key'], $t['default']) ? 'checked' : '' ?>>
                        <span class="prof-switch-track"></span>
                    </label>
                </div>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- ── Privacy ────────────────────────────────────── -->
        <div class="prof-card">
            <div class="prof-card-head">
                <div>
                    <h3 class="prof-card-title">Privacy</h3>
                    <p class="prof-card-sub">Control what others can see about you</p>
                </div>
            </div>
            <div class="prof-card-body">
                <div class="prof-info-grid">
                    <div class="prof-info-row">
                        <span class="prof-info-label">Profile visibility</span>
                        <span class="prof-info-val">Landlords only</span>
                    </div>
                    <div class="prof-info-row">
                        <span class="prof-info-label">Phone number</span>
                        <span class="prof-info-val">Hidden from public</span>
                    </div>
                    <div class="prof-info-row">
                        <span class="prof-info-label">Email address</span>
                        <span class="prof-info-val">Hidden from public</span>
                    </div>
                    <div class="prof-info-row">
                        <span class="prof-info-label">Viewing history</span>
                        <span class="prof-info-val">Private</span>
                    </div>
                </div>
                <p class="prof-field-hint" style="margin-top:12px">Privacy settings are managed by the platform to protect your data. Contact support to request changes.</p>
            </div>
        </div>

        <!-- ── Account Information ────────────────────────── -->
        <div class="prof-card">
            <div class="prof-card-head">
                <div>
                    <h3 class="prof-card-title">Account Information</h3>
                    <p class="prof-card-sub">Read-only details about your account</p>
                </div>
            </div>
            <div class="prof-card-body">
                <div class="prof-info-grid">
                    <div class="prof-info-row">
                        <span class="prof-info-label">Account ID</span>
                        <span class="prof-info-val prof-mono">#<?= str_pad((string)($renter['user_id'] ?? 0), 5, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="prof-info-row">
                        <span class="prof-info-label">Account type</span>
                        <span class="prof-info-val">Renter</span>
                    </div>
                    <div class="prof-info-row">
                        <span class="prof-info-label">Member since</span>
                        <span class="prof-info-val"><?= !empty($renter['created_at']) ? date('d M Y', strtotime($renter['created_at'])) : '—' ?></span>
                    </div>
                    <div class="prof-info-row">
                        <span class="prof-info-label">Last login</span>
                        <span class="prof-info-val"><?= !empty($renter['last_login_at']) ? date('d M Y H:i', strtotime($renter['last_login_at'])) : 'Unknown' ?></span>
                    </div>
                    <div class="prof-info-row">
                        <span class="prof-info-label">Sign-in method</span>
                        <span class="prof-info-val"><?= ($renter['auth_provider'] ?? 'local') === 'google' ? 'Google' : 'Email & password' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="prof-save-row">
            <button type="submit" class="btn btn-primary">Save settings</button>
        </div>

    </form>
</div>
