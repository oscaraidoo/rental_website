<?php
/**
 * Multi-channel notification module tests (mocked providers — no real sends).
 * Run: php storage/tests/multichannel_notification_test.php
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
$_ENV['APP_DEBUG'] = 'true';
$_ENV['MAIL_DRIVER'] = 'log';
$_ENV['SMS_PROVIDER'] = 'log'; // mock/log driver — never a real gateway

require ROOT_PATH . '/bootstrap/app.php';

use App\Helpers\Database;
use App\Services\{NotificationService, NotificationTemplates, SmsProvider, EmailProvider};

$pass = 0; $fail = 0;
function check(string $name, bool $cond): void {
    global $pass, $fail;
    if ($cond) { $pass++; echo "PASS — $name\n"; }
    else       { $fail++; echo "FAIL — $name\n"; }
}

// ── Test user (ephemeral; drop any leftover copy for idempotent reruns) ─────
Database::query("DELETE FROM users WHERE email = 'multichan_test_local@example.com'");
Database::query(
    "INSERT INTO users (role_id, first_name, last_name, email, phone, password, email_verified, status)
     VALUES (4, 'Test', 'Notif', 'multichan_test_local@example.com', '0244000011', ?, 1, 'active')",
    [password_hash('x', PASSWORD_DEFAULT)]
);
$uid = (int) Database::fetchColumn("SELECT id FROM users WHERE email = 'multichan_test_local@example.com'");

try {
    // ── Template rendering ────────────────────────────────
    $out = NotificationTemplates::render('Hi {{userName}}, order {{ref}}.', ['userName' => 'Ama <b>', 'ref' => 'R1']);
    check('render substitutes vars', $out === 'Hi Ama <b>, order R1.');

    $out = NotificationTemplates::render('Hi {{unknown_var}}', []);
    check('render strips unresolved placeholders', !str_contains($out, '{{'));

    check('has() knows known type', NotificationTemplates::has('PAYMENT_SUCCESSFUL'));
    check('has() rejects unknown type', !NotificationTemplates::has('NOT_A_TYPE'));

    // ── Phone validation / normalisation ──────────────────
    check('valid E164 accepted', SmsProvider::isValidE164('+233244000011'));
    check('invalid E164 rejected', !SmsProvider::isValidE164('0244000011'));
    check('normalise local 0-prefix', SmsProvider::normalizeToE164('024 400 0011') === '+233244000011');
    check('normalise +233 stays', SmsProvider::normalizeToE164('+233244000011') === '+233244000011');
    check('normalise garbage returns null', SmsProvider::normalizeToE164('not-a-phone') === null);

    // ── Email provider validation (mock path = log driver) ─
    [$ok] = EmailProvider::send('not-an-email', 's', '<p>b</p>', 'b');
    check('email provider rejects invalid address', !$ok);
    [$ok, $pid] = EmailProvider::send('multichan_test_local@example.com', 's', '<p>b</p>', 'b');
    check('email provider (log driver) succeeds', $ok && str_starts_with((string)$pid, 'mail-'));

    // ── SMS provider validation ───────────────────────────
    [$ok, , $err] = SmsProvider::send('0244000011', 'hi');
    check('sms provider rejects non-E164', !$ok && str_contains($err, 'Invalid phone'));
    [$ok, $pid] = SmsProvider::send('+233244000011', 'hi');
    check('sms provider (log driver) succeeds', $ok && str_starts_with((string)$pid, 'log-'));

    // ── Preferences defaults + updates ────────────────────
    $p = NotificationService::preferencesWithPhone($uid);
    check('default prefs all enabled', $p['email_enabled'] && $p['sms_enabled'] && $p['muted_types'] === []);
    check('phone auto-normalised from users.phone', $p['phone_e164'] === '+233244000011');

    $r = NotificationService::updatePreferences($uid, ['phone' => 'invalid']);
    check('invalid phone update rejected', !$r['ok']);
    $r = NotificationService::updatePreferences($uid, ['email_enabled' => false, 'muted_types' => ['PAYMENT_SUCCESSFUL', 'BOGUS_TYPE']]);
    check('prefs update ok', $r['ok']);
    $p = NotificationService::preferences($uid);
    check('email toggle persisted', !$p['email_enabled']);
    check('muted list persisted + bogus filtered', $p['muted_types'] === ['PAYMENT_SUCCESSFUL']);

    // ── Disabled channel → skipped (no row) ───────────────
    $before = (int) Database::fetchColumn("SELECT COUNT(*) FROM notification_outbound WHERE user_id = ?", [$uid]);
    NotificationService::send($uid, 'PAYMENT_SUCCESSFUL', ['amount' => 'GH₵10', 'purpose' => 'test', 'reference' => 'X'], ['email'], 'idem-email-off');
    $after = (int) Database::fetchColumn("SELECT COUNT(*) FROM notification_outbound WHERE user_id = ?", [$uid]);
    check('disabled email channel skipped', $before === $after);

    NotificationService::updatePreferences($uid, ['email_enabled' => true, 'muted_types' => []]);
    $r = NotificationService::send($uid, 'PAYMENT_SUCCESSFUL', ['amount' => 'GH₵10', 'purpose' => 'test', 'reference' => 'X1'], ['email'], 'idem-a');
    check('email send logged with id', $r['email'] !== null);
    $row = Database::fetchOne("SELECT * FROM notification_outbound WHERE id = ?", [$r['email']]);
    check('row status sent (log driver)', $row['status'] === 'sent');
    check('row channel + recipient correct', $row['channel'] === 'email' && $row['recipient'] === 'multichan_test_local@example.com');

    // ── Duplicate prevention (idempotency) ────────────────
    $r2 = NotificationService::send($uid, 'PAYMENT_SUCCESSFUL', ['amount' => 'GH₵10', 'purpose' => 'test', 'reference' => 'X1'], ['email'], 'idem-a');
    check('duplicate send skipped (idempotency)', $r2['email'] === null);

    // ── Muted type → skipped ──────────────────────────────
    NotificationService::updatePreferences($uid, ['muted_types' => ['PAYMENT_SUCCESSFUL']]);
    $r3 = NotificationService::send($uid, 'PAYMENT_SUCCESSFUL', [], ['email'], 'idem-muted');
    check('muted type skipped', $r3['email'] === null);
    // (restore unmuted state for later tests)
    NotificationService::updatePreferences($uid, ['muted_types' => []]);

    // ── Unknown type → failure logged, no throw ───────────
    $r4 = NotificationService::send($uid, 'TOTALLY_UNKNOWN', [], ['email']);
    check('unknown type returns nulls safely', $r4['email'] === null);
    $bad = Database::fetchOne("SELECT id FROM notification_outbound WHERE notification_type = 'TOTALLY_UNKNOWN' LIMIT 1");
    check('unknown type failure logged', $bad !== null);

    // ── Both channels ─────────────────────────────────────
    $r5 = NotificationService::send($uid, 'VIEWING_CONFIRMED', ['propertyTitle' => 'Villa', 'date' => '2026-10-10', 'time' => '10:00'], ['email', 'sms'], 'idem-both');
    check('both channels dispatched', $r5['email'] !== null && $r5['sms'] !== null);
    $smsRow = Database::fetchOne("SELECT * FROM notification_outbound WHERE id = ?", [$r5['sms']]);
    check('sms row uses normalised phone', $smsRow['recipient'] === '+233244000011');
    check('sms body rendered from template', str_contains($smsRow['body_preview'], 'Villa'));

    // ── History + filters ─────────────────────────────────
    $hist = NotificationService::history(10, 0, ['user_id' => $uid, 'channel' => 'sms']);
    check('history filters by channel', count($hist) >= 1 && $hist[0]['channel'] === 'sms');
    $cnt = NotificationService::historyCount(['user_id' => $uid, 'status' => 'sent']);
    check('history count works', $cnt >= 2);

} finally {
    // ── Cleanup ───────────────────────────────────────────
    Database::query("DELETE FROM notification_outbound WHERE user_id = ?", [$uid]);
    Database::query("DELETE FROM notification_channel_prefs WHERE user_id = ?", [$uid]);
    Database::query("DELETE FROM users WHERE id = ?", [$uid]);
}

echo $fail === 0 ? "\nALL PASS ($pass)\n" : "\n$fail FAILURES, $pass passed\n";
exit($fail === 0 ? 0 : 1);
