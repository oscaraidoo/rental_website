<?php use App\Helpers\{Url, Security}; ?>
<?php include __DIR__ . '/_layout.php'; ?>

<div class="prof-body">

    <!-- ── Account Status ─────────────────────────────────── -->
    <div class="prof-card">
        <div class="prof-card-head">
            <div>
                <h3 class="prof-card-title">Account Status</h3>
                <p class="prof-card-sub">Your current account standing</p>
            </div>
        </div>
        <div class="prof-card-body">
            <?php
            $status = $renter['user_status'] ?? $renter['status'] ?? 'active';
            $statusDetails = [
                'active'               => ['icon' => 'ok',      'color' => '#16a34a', 'bg' => '#f0fdf4', 'title' => 'Active',               'desc' => 'Your account is in good standing. You have full access to all features.'],
                'inactive'             => ['icon' => 'off',     'color' => '#64748b', 'bg' => '#f8fafc', 'title' => 'Inactive',             'desc' => 'Your account has been deactivated. Contact support to reactivate.'],
                'suspended'            => ['icon' => 'banned',  'color' => '#dc2626', 'bg' => '#fef2f2', 'title' => 'Suspended',            'desc' => 'Your account has been suspended by an administrator. Contact support for assistance.'],
                'pending_verification' => ['icon' => 'pending', 'color' => '#d97706', 'bg' => '#fffbeb', 'title' => 'Pending Verification', 'desc' => 'Please verify your email address to activate your account.'],
            ];
            $sd = $statusDetails[$status] ?? $statusDetails['active'];

            $statusIcons = [
                'ok'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>',
                'off'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
                'banned'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>',
                'pending' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
            ];
            ?>
            <div class="prof-status-detail" style="background:<?= $sd['bg'] ?>;border-color:<?= $sd['color'] ?>20">
                <span class="prof-status-detail-icon" style="color:<?= $sd['color'] ?>"><?= $statusIcons[$sd['icon']] ?></span>
                <div>
                    <div style="font-size:14px;font-weight:700;color:<?= $sd['color'] ?>"><?= $sd['title'] ?></div>
                    <div style="font-size:13px;color:var(--muted);margin-top:3px"><?= $sd['desc'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Export your data ───────────────────────────────── -->
    <div class="prof-card">
        <div class="prof-card-head">
            <div>
                <h3 class="prof-card-title">Your Data</h3>
                <p class="prof-card-sub">What we hold about your account</p>
            </div>
        </div>
        <div class="prof-card-body">
            <div class="prof-info-grid">
                <div class="prof-info-row">
                    <span class="prof-info-label">Personal information</span>
                    <span class="prof-info-val">Name, email, phone, address</span>
                </div>
                <div class="prof-info-row">
                    <span class="prof-info-label">Viewing requests</span>
                    <span class="prof-info-val">Records retained for business purposes</span>
                </div>
                <div class="prof-info-row">
                    <span class="prof-info-label">Payments</span>
                    <span class="prof-info-val">Records retained for legal compliance</span>
                </div>
                <div class="prof-info-row">
                    <span class="prof-info-label">Reviews</span>
                    <span class="prof-info-val">Retained anonymously after account closure</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Deactivate Account ─────────────────────────────── -->
    <?php if ($status === 'active'): ?>
    <div class="prof-card prof-card--danger">
        <div class="prof-card-head">
            <div>
                <h3 class="prof-card-title" style="color:#dc2626">Deactivate Account</h3>
                <p class="prof-card-sub">Temporarily disable your account. You can reactivate by contacting support.</p>
            </div>
        </div>
        <div class="prof-card-body">
            <div class="prof-danger-info">
                <p><strong>Before you deactivate, please note:</strong></p>
                <ul>
                    <li>You will be immediately signed out</li>
                    <li>You will not be able to sign in until reactivated by support</li>
                    <li>Your saved properties and viewing history will be preserved</li>
                    <li>Active viewing requests will not be automatically cancelled</li>
                    <li>Your payment records are retained for compliance purposes</li>
                </ul>
            </div>

            <button type="button" class="prof-danger-btn" id="showDeactivateBtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                Deactivate my account
            </button>

            <!-- Confirmation panel (hidden by default) -->
            <div class="prof-confirm-panel" id="deactivatePanel" style="display:none">
                <div class="prof-confirm-panel-head">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <strong>Confirm account deactivation</strong>
                </div>
                <form method="POST" action="<?= Url::base('renter/profile/deactivate') ?>">
                    <?= Security::csrfField() ?>
                    <div class="prof-field" style="margin-bottom:14px">
                        <label>Reason (optional)</label>
                        <select name="reason">
                            <option value="">Select a reason</option>
                            <option value="found_property">I found a property</option>
                            <option value="not_using">Not using the platform</option>
                            <option value="privacy">Privacy concerns</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="prof-field" style="margin-bottom:18px">
                        <label>Enter your password to confirm <span class="prof-req">*</span></label>
                        <input type="password" name="password" required autocomplete="current-password" placeholder="Your current password">
                    </div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap">
                        <button type="submit" class="prof-danger-btn">Yes, deactivate my account</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('deactivatePanel').style.display='none';document.getElementById('showDeactivateBtn').style.display='inline-flex'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
document.getElementById('showDeactivateBtn')?.addEventListener('click', function () {
    document.getElementById('deactivatePanel').style.display = 'block';
    this.style.display = 'none';
});
</script>
