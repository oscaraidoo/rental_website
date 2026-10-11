<?php use App\Helpers\{Url, Security}; use App\Services\NotificationTemplates; ?>
<?php /** @var array $prefs @var ?array $user @var array $catalogue */ ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-0">Notification Settings</h5>
        <p class="text-muted small mb-0">Choose how and when RentalWebsite contacts you.</p>
    </div>
</div>

<form method="POST" action="<?= Url::base('renter/notifications/settings') ?>">
    <?= Security::csrfField() ?>

    <!-- Channels -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Channels</h6>

            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <div style="font-weight:600">Email notifications</div>
                    <div class="text-muted small"><?= Security::e($user['email'] ?? 'No email on file') ?></div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="email_enabled" <?= $prefs['email_enabled'] ? 'checked' : '' ?>>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <div style="font-weight:600">SMS notifications</div>
                    <div class="text-muted small">Standard carrier rates may apply</div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="sms_enabled" <?= $prefs['sms_enabled'] ? 'checked' : '' ?>>
                </div>
            </div>

            <div class="py-2">
                <label class="form-label" style="font-weight:600">Phone number (for SMS)</label>
                <div class="input-group" style="max-width:340px">
                    <span class="input-group-text">+233</span>
                    <input type="text" class="form-control" name="phone_local"
                           placeholder="XX XXX XXXX"
                           value="<?= Security::e($prefs['phone_e164'] ? preg_replace('/^\+233/', '', $prefs['phone_e164']) : '') ?>">
                </div>
                <div class="form-text">Ghana numbers by default. Include your full number if abroad.</div>
            </div>
        </div>
    </div>

    <!-- Types -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-1">Notification types</h6>
            <p class="text-muted small mb-3">Untick a type to stop receiving it (on all channels).</p>

            <?php foreach ($catalogue as $group => $types): ?>
            <div class="mb-3">
                <div class="text-muted small text-uppercase mb-1" style="letter-spacing:.05em"><?= Security::e($group) ?></div>
                <?php foreach ($types as $t): ?>
                <div class="d-flex justify-content-between align-items-center py-1">
                    <div>
                        <span style="font-weight:500"><?= Security::e($t['label']) ?></span>
                        <span class="text-muted small ms-1">(<?= implode(', ', $t['channels']) ?>)</span>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="muted_<?= Security::e($t['type']) ?>"
                               value="1" <?= !in_array($t['type'], $prefs['muted_types'], true) ? 'checked' : '' ?>>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button class="btn btn-primary">Save settings</button>
</form>

<!-- Test sends -->
<div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
        <h6 class="fw-bold mb-1">Test your settings</h6>
        <p class="text-muted small">Send a one-off test notification to verify delivery. Limited to 3 per 10 minutes.</p>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="testEmailBtn">Send test email</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="testSmsBtn">Send test SMS</button>
        </div>
        <div id="testResult" class="small mt-2"></div>
    </div>
</div>

<script>
document.getElementById('testEmailBtn').addEventListener('click', () => sendTest('email'));
document.getElementById('testSmsBtn').addEventListener('click', () => sendTest('sms'));

function sendTest(channel) {
    const out = document.getElementById('testResult');
    out.textContent = 'Sending…';
    const body = new URLSearchParams({ channel, csrf_token: '<?= Security::csrfToken() ?>' });
    fetch('<?= Url::base('api/notification-settings/test') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        credentials: 'same-origin',
    })
    .then(r => r.json())
    .then(d => {
        out.textContent = d.message || (d.success ? 'Sent.' : 'Failed.');
        out.style.color = d.success ? '#16a34a' : '#dc2626';
    })
    .catch(() => { out.textContent = 'Request failed.'; out.style.color = '#dc2626'; });
}
</script>
