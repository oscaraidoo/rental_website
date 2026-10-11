<?php
/**
 * Multi-channel Notification Service (Email + SMS).
 *
 * Single entry point for every feature that needs to notify a user:
 *
 *   NotificationService::send(
 *       userId:  $userId,
 *       type:    'PAYMENT_SUCCESSFUL',
 *       data:    ['userName' => 'Ama', 'amount' => 'GH₵200.00', ...],
 *       channels:['email','sms'],   // optional; defaults to template channels
 *   );
 *
 * Responsibilities (all in one place, never duplicated in features):
 *   - validates the template type and requested channels
 *   - loads the user + their channel preferences and gates delivery
 *   - renders email (HTML + text) and SMS from NotificationTemplates
 *   - dispatches via EmailProvider / SmsProvider
 *   - records one notification_outbound row per channel with status
 *   - retries transient failures up to MAX_ATTEMPTS with backoff-free
 *     immediate re-try (queue-friendly; safe to call from a cron worker)
 *   - dedupes via idempotency_key (type + key data hash per user)
 *
 * Failure contract: send() NEVER throws and NEVER aborts the caller's
 * business operation — all provider errors are caught and logged.
 */

declare(strict_types=1);

namespace App\Services;

use App\Helpers\{Database, Security};

class NotificationService
{
    private const MAX_ATTEMPTS = 3;

    // ── Public API ───────────────────────────────────────────

    /**
     * Send a notification through the requested channels.
     *
     * @param int         $userId   recipient user id
     * @param string      $type     template type id (e.g. 'PAYMENT_SUCCESSFUL')
     * @param array       $data     template variables (already safe values)
     * @param array|null  $channels subset of ['email','sms']; null = template default
     * @param string|null $idemKey  optional idempotency key; duplicates within
     *                              24h are silently skipped per channel
     * @return array{email:?int, sms:?int}  outbound log row ids (null = skipped/not attempted)
     */
    public static function send(int $userId, string $type, array $data = [], ?array $channels = null, ?string $idemKey = null): array
    {
        $result = ['email' => null, 'sms' => null];

        try {
            if (!NotificationTemplates::has($type)) {
                self::logRow(null, $type, 'email', '-', null, null, null, 'failed', null, 0, null, "Unknown notification type '{$type}'");
                return $result;
            }

            $user = Database::fetchOne(
                "SELECT id, email, phone FROM users WHERE id = ?",
                [$userId]
            );
            if (!$user) {
                return $result;
            }

            $prefs  = self::preferences($userId);
            $wanted = $channels ?? NotificationTemplates::TEMPLATES[$type]['channels'];
            $data  += ['userName' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))];

            if (in_array('email', $wanted, true)) {
                $result['email'] = self::dispatch(
                    (int) $userId, $type, 'email', (string) ($user['email'] ?? ''),
                    $data, $prefs, $idemKey
                );
            }
            if (in_array('sms', $wanted, true)) {
                $phone = ($prefs['phone_e164'] ?? null) ?: SmsProvider::normalizeToE164((string) ($user['phone'] ?? ''));
                if ($phone === null) {
                    error_log('[NotificationService] SMS skipped: no valid phone for user ' . $userId);
                } else {
                    $result['sms'] = self::dispatch((int) $userId, $type, 'sms', $phone, $data, $prefs, $idemKey);
                }
            }
        } catch (\Throwable $e) {
            // Notifications must never break the main operation.
            error_log('[NotificationService] ' . get_class($e) . ': ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Send an announcement to multiple users (admin only). Failures per user
     * are isolated; one bad row never stops the loop.
     *
     * @param int[] $userIds
     * @return array{sent:int, failed:int}
     */
    public static function announce(array $userIds, string $subject, string $message, array $channels = ['email']): array
    {
        $sent = $failed = 0;
        foreach ($userIds as $uid) {
            $r = self::send((int) $uid, 'ADMIN_ANNOUNCEMENT', [
                'title'   => $subject,
                'message' => $message,
            ], $channels);
            foreach (['email', 'sms'] as $ch) {
                if ($r[$ch] !== null) {
                    // row id present; success/failure already logged — count via status
                }
            }
            // recount from log for accuracy
            $row = Database::fetchOne(
                "SELECT status FROM notification_outbound WHERE id = ? LIMIT 1",
                [$r['email'] ?? $r['sms'] ?? 0]
            );
            if ($row && $row['status'] === 'sent') { $sent++; } else { $failed++; }
        }
        return ['sent' => $sent, 'failed' => $failed];
    }

    // ── Channel dispatch (prefs gate → render → send → log → retry) ──

    private static function dispatch(int $userId, string $type, string $channel, string $recipient, array $data, array $prefs, ?string $idemKey): ?int
    {
        $def = NotificationTemplates::TEMPLATES[$type];

        // Channel gate (global toggle + per-type mute)
        if ($channel === 'email' && !$prefs['email_enabled']) return null;
        if ($channel === 'sms'   && !$prefs['sms_enabled'])   return null;
        if (in_array($type, $prefs['muted_types'], true))      return null;
        if ($recipient === '')                                 return null;

        // Idempotency: same user+key+channel within 24h already attempted → skip
        // (channel-scoped so a combined email+SMS send doesn't drop SMS)
        $key = $idemKey ? mb_substr($channel . '|' . $type . '|' . $idemKey, 0, 150) : null;
        if ($key !== null) {
            $dup = Database::fetchColumn(
                "SELECT id FROM notification_outbound
                 WHERE user_id = ? AND idempotency_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                 LIMIT 1",
                [$userId, $key]
            );
            if ($dup) return null;
        }

        // Render
        $html = $text = $subject = null;
        if ($channel === 'email') {
            $subject = NotificationTemplates::render($def['subject'], $data, false);
            $text    = NotificationTemplates::render($def['message'], $data, false);
            $html    = self::renderEmailHtml($def, $data, $text);
        } else {
            $text = mb_substr(NotificationTemplates::render($def['sms'] ?? $def['message'], $data, false), 0, 320);
        }

        // Insert pending row first (crash-safe audit trail)
        $logId = self::logRow($userId, $type, $channel, $recipient, $subject, $text, $def['group'] ?? null, 'pending', null, 0, $key, null);

        // Send + retry loop (immediate retries; a cron worker can re-drive later)
        $attempt = 0;
        $lastErr = null;
        $providerId = null;
        $ok = false;
        while ($attempt < self::MAX_ATTEMPTS) {
            $attempt++;
            if ($channel === 'email') {
                [$ok, $providerId, $lastErr] = EmailProvider::send($recipient, (string) $subject, (string) $html, (string) $text);
            } else {
                [$ok, $providerId, $lastErr] = SmsProvider::send($recipient, (string) $text);
            }
            if ($ok) break;
            $lastErr = self::sanitizeError((string) $lastErr);
        }

        $status = $ok ? 'sent' : ($attempt >= self::MAX_ATTEMPTS ? 'failed' : 'pending');
        Database::update('notification_outbound', [
            'status'              => $status,
            'provider'            => $channel === 'email' ? EmailProvider::driverName() : SmsProvider::driverName(),
            'provider_message_id' => $ok ? $providerId : null,
            'error_message'       => $ok ? null : mb_substr((string) $lastErr, 0, 500),
            'attempts'            => $attempt,
            'sent_at'             => $ok ? date('Y-m-d H:i:s') : null,
        ], 'id = ?', [$logId]);

        return $logId;
    }

    /** Rich HTML email: dedicated view if present, else generic branded wrapper. */
    private static function renderEmailHtml(array $def, array $data, string $plain): string
    {
        $view = $def['email_view'] ?? null;
        if ($view && is_file(ROOT_PATH . '/app/Views/emails/' . $view . '.php')) {
            extract($data, EXTR_SKIP);
            ob_start();
            include ROOT_PATH . '/app/Views/emails/' . $view . '.php';
            return (string) ob_get_clean();
        }

        $appName = $_ENV['APP_NAME'] ?? 'RentalWebsite';
        $msg     = NotificationTemplates::render($def['message'], $data, true);
        return '<div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;padding:24px;">'
            . '<h2 style="color:#051F20;margin:0 0 16px;font-size:18px;">' . NotificationTemplates::render($def['subject'], $data, true) . '</h2>'
            . '<p style="color:#33443b;font-size:14px;line-height:1.6;margin:0 0 24px;">' . $msg . '</p>'
            . '<p style="color:#8ba092;font-size:12px;margin:0;">— ' . Security::e($appName) . '</p>'
            . '</div>';
    }

    // ── Preferences ──────────────────────────────────────────

    /**
     * Channel preferences for a user. Missing row = all enabled (defaults).
     * @return array{email_enabled:bool, sms_enabled:bool, phone_e164:?string, muted_types:string[]}
     */
    public static function preferences(int $userId): array
    {
        $row = Database::fetchOne(
            "SELECT email_enabled, sms_enabled, phone_e164, muted_types FROM notification_channel_prefs WHERE user_id = ?",
            [$userId]
        );
        if (!$row) {
            return ['email_enabled' => true, 'sms_enabled' => true, 'phone_e164' => null, 'muted_types' => []];
        }
        $muted = json_decode((string) ($row['muted_types'] ?? '[]'), true);
        return [
            'email_enabled' => (bool) $row['email_enabled'],
            'sms_enabled'   => (bool) $row['sms_enabled'],
            'phone_e164'    => ($row['phone_e164'] ?? null) ?: null,
            'muted_types'   => is_array($muted) ? array_values(array_filter($muted, 'is_string')) : [],
        ];
    }

    /**
     * Preferences with phone fallback: when the user has no stored E.164
     * phone yet, normalise users.phone on the fly (E.164-correct since it
     * goes through the shared normaliser).
     */
    public static function preferencesWithPhone(int $userId): array
    {
        $prefs = self::preferences($userId);
        if ($prefs['phone_e164'] === null) {
            $user = Database::fetchOne("SELECT phone FROM users WHERE id = ?", [$userId]);
            $prefs['phone_e164'] = $user ? SmsProvider::normalizeToE164((string) ($user['phone'] ?? '')) : null;
        }
        return $prefs;
    }

    /**
     * Update preferences. Only provided keys are changed.
     * @param array{email_enabled?:bool, sms_enabled?:bool, phone?:?string, muted_types?:string[]}|null $patch
     */
    public static function updatePreferences(int $userId, ?array $patch): array
    {
        // Ensure row exists
        $exists = Database::fetchColumn("SELECT user_id FROM notification_channel_prefs WHERE user_id = ?", [$userId]);
        if (!$exists) {
            Database::insert('notification_channel_prefs', ['user_id' => $userId]);
        }

        $update = [];
        if (array_key_exists('email_enabled', $patch)) $update['email_enabled'] = $patch['email_enabled'] ? 1 : 0;
        if (array_key_exists('sms_enabled', $patch))   $update['sms_enabled']   = $patch['sms_enabled'] ? 1 : 0;
        if (array_key_exists('phone', $patch)) {
            $phone = $patch['phone'] ? SmsProvider::normalizeToE164((string) $patch['phone']) : null;
            if ($patch['phone'] && $phone === null) {
                return ['ok' => false, 'error' => 'Invalid phone number. Use format +233 XX XXX XXXX.'];
            }
            $update['phone_e164'] = $phone;
        }
        if (array_key_exists('muted_types', $patch)) {
            $valid = array_values(array_filter((array) $patch['muted_types'], fn($t) => NotificationTemplates::has((string) $t)));
            $update['muted_types'] = json_encode($valid);
        }

        if ($update) {
            Database::update('notification_channel_prefs', $update, 'user_id = ?', [$userId]);
        }
        return ['ok' => true];
    }

    // ── History / admin ──────────────────────────────────────

    public static function history(int $limit = 50, int $offset = 0, array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['channel'])) { $where[] = 'o.channel = ?';          $params[] = $filters['channel']; }
        if (!empty($filters['type']))    { $where[] = 'o.notification_type = ?'; $params[] = $filters['type']; }
        if (!empty($filters['status']))  { $where[] = 'o.status = ?';           $params[] = $filters['status']; }
        if (!empty($filters['user_id'])) { $where[] = 'o.user_id = ?';          $params[] = (int) $filters['user_id']; }

        $params[] = min(200, max(1, $limit));
        $params[] = max(0, $offset);

        return Database::fetchAll(
            "SELECT o.*, u.email AS user_email
             FROM notification_outbound o
             LEFT JOIN users u ON u.id = o.user_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY o.created_at DESC LIMIT ? OFFSET ?",
            $params
        );
    }

    public static function historyCount(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['channel'])) { $where[] = 'channel = ?';        $params[] = $filters['channel']; }
        if (!empty($filters['type']))    { $where[] = 'notification_type = ?'; $params[] = $filters['type']; }
        if (!empty($filters['status']))  { $where[] = 'status = ?';         $params[] = $filters['status']; }
        if (!empty($filters['user_id'])) { $where[] = 'user_id = ?';        $params[] = (int) $filters['user_id']; }
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM notification_outbound WHERE " . implode(' AND ', $where), $params
        );
    }

    /**
     * Retry a failed notification (admin action). Re-renders from stored
     * template + previewed text so retries never re-run business logic.
     */
    public static function retry(int $logId): bool
    {
        $row = Database::fetchOne("SELECT * FROM notification_outbound WHERE id = ?", [$logId]);
        if (!$row || $row['status'] === 'sent') return false;

        $ok = false; $pid = null; $err = null;
        if ($row['channel'] === 'email') {
            [$ok, $pid, $err] = EmailProvider::send($row['recipient'], (string) $row['subject'], (string) $row['body_preview'], (string) $row['body_preview']);
        } else {
            [$ok, $pid, $err] = SmsProvider::send($row['recipient'], (string) $row['body_preview']);
        }

        Database::update('notification_outbound', [
            'status'              => $ok ? 'sent' : 'failed',
            'provider'            => $row['channel'] === 'email' ? EmailProvider::driverName() : SmsProvider::driverName(),
            'provider_message_id' => $ok ? $pid : $row['provider_message_id'],
            'error_message'       => $ok ? null : self::sanitizeError((string) $err),
            'attempts'            => ((int) $row['attempts']) + 1,
            'sent_at'             => $ok ? date('Y-m-d H:i:s') : null,
        ], 'id = ?', [$logId]);

        return $ok;
    }

    // ── Internals ────────────────────────────────────────────

    private static function logRow(?int $userId, string $type, string $channel, string $recipient, ?string $subject, ?string $body, ?string $template, string $status, ?string $providerId, int $attempts, ?string $idemKey, ?string $error): int
    {
        return (int) Database::insert('notification_outbound', [
            'user_id'             => $userId,
            'notification_type'   => mb_substr($type, 0, 80),
            'channel'             => $channel,
            'recipient'           => mb_substr($recipient, 0, 191),
            'subject'             => $subject !== null ? mb_substr($subject, 0, 255) : null,
            'body_preview'        => $body !== null ? mb_substr($body, 0, 500) : null,
            'template'            => $template,
            'status'              => $status,
            'provider'            => $providerId,
            'provider_message_id' => null,
            'error_message'       => $error !== null ? mb_substr($error, 0, 500) : null,
            'attempts'            => $attempts,
            'idempotency_key'     => $idemKey,
        ]);
    }

    /** Strip anything credential-shaped out of provider error strings. */
    private static function sanitizeError(string $err): string
    {
        $err = preg_replace('/(Bearer\s+)[A-Za-z0-9\-._~+\/]+=*/i', '$1[redacted]', $err) ?? $err;
        $err = preg_replace('/(key|token|secret|password)(["\']?\s*[:=]\s*["\']?)[^\s"\',}]+/i', '$1$2[redacted]', $err) ?? $err;
        return trim($err);
    }

    // ── In-app notification helpers ───────────────────────────

    /**
     * Count unread in-app notifications for a user.
     */
    public static function unreadCount(int $userId): int
    {
        if ($userId <= 0) return 0;
        try {
            return (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM notifications
                 WHERE user_id = ? AND is_read = 0 AND channel = 'in_app'",
                [$userId]
            );
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Fetch the most recent in-app notifications for a user.
     */
    public static function recent(int $userId, int $limit = 6): array
    {
        if ($userId <= 0) return [];
        try {
            return Database::fetchAll(
                "SELECT id, type, title, body, is_read, created_at
                 FROM notifications
                 WHERE user_id = ? AND channel = 'in_app'
                 ORDER BY created_at DESC
                 LIMIT ?",
                [$userId, $limit]
            );
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Mark a single in-app notification as read.
     */
    public static function markRead(int $notificationId, int $userId): void
    {
        try {
            Database::query(
                "UPDATE notifications SET is_read = 1, read_at = NOW()
                 WHERE id = ? AND user_id = ?",
                [$notificationId, $userId]
            );
        } catch (\Throwable) { }
    }

    /**
     * Mark all in-app notifications as read for a user.
     */
    public static function markAllRead(int $userId): void
    {
        try {
            Database::query(
                "UPDATE notifications SET is_read = 1, read_at = NOW()
                 WHERE user_id = ? AND is_read = 0 AND channel = 'in_app'",
                [$userId]
            );
        } catch (\Throwable) { }
    }

    /**
     * Create an in-app notification directly.
     */
    public static function create(int $userId, string $type, string $title, string $body, array $data = []): void
    {
        try {
            Database::insert('notifications', [
                'user_id'    => $userId,
                'type'       => mb_substr($type, 0, 80),
                'title'      => mb_substr($title, 0, 255),
                'body'       => $body,
                'data'       => !empty($data) ? json_encode($data) : null,
                'channel'    => 'in_app',
                'is_read'    => 0,
                'sent'       => 1,
                'sent_at'    => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable) { }
    }

    // ── forUser (paginated in-app feed) ──────────────────────

    public static function forUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        if ($userId <= 0) return [];
        try {
            return Database::fetchAll(
                "SELECT id, type, title, body, data, is_read, created_at
                 FROM notifications
                 WHERE user_id = ? AND channel = 'in_app'
                 ORDER BY created_at DESC
                 LIMIT ? OFFSET ?",
                [$userId, $limit, $offset]
            );
        } catch (\Throwable) { return []; }
    }

    // ── Domain-specific notification shortcuts ────────────────
    // These create an in-app notification and can be extended to
    // trigger email/SMS via self::send() when templates exist.

    public static function propertyApproved(int $userId, string $title, int $propertyId, string $slug = ''): void
    {
        self::create($userId, 'property_approved',
            'Property approved',
            "Your property \"{$title}\" has been approved and is now live.",
            ['property_id' => $propertyId, 'slug' => $slug]
        );
    }

    public static function propertyRejected(int $userId, string $title, string $reason, int $propertyId): void
    {
        self::create($userId, 'property_rejected',
            'Property not approved',
            "Your property \"{$title}\" was not approved. Reason: {$reason}",
            ['property_id' => $propertyId]
        );
    }

    public static function propertyVerified(int $userId, string $title, int $propertyId, string $ref): void
    {
        self::create($userId, 'property_verified',
            'Property verified',
            "Your property \"{$title}\" has been physically verified. Reference: {$ref}",
            ['property_id' => $propertyId, 'ref' => $ref]
        );
    }

    public static function propertyFeatured(int $userId, string $title, int $propertyId, string $until): void
    {
        self::create($userId, 'property_featured',
            'Property featured',
            "Your property \"{$title}\" is now featured on the homepage until {$until}.",
            ['property_id' => $propertyId]
        );
    }

    public static function propertySuspended(int $userId, string $title, string $reason, int $propertyId): void
    {
        self::create($userId, 'property_suspended',
            'Property suspended',
            "Your property \"{$title}\" has been suspended. Reason: {$reason}",
            ['property_id' => $propertyId]
        );
    }

    public static function viewingRequested(int $userId, string $renterName, string $propertyTitle, int $viewingId): void
    {
        self::create($userId, 'viewing_requested',
            'New viewing request',
            "{$renterName} has requested a viewing for \"{$propertyTitle}\".",
            ['viewing_id' => $viewingId]
        );
    }

    public static function viewingConfirmed(int $userId, string $propertyTitle, string $date, string $time, int $viewingId): void
    {
        self::create($userId, 'viewing_confirmed',
            'Viewing confirmed',
            "Your viewing for \"{$propertyTitle}\" is confirmed on {$date} at {$time}.",
            ['viewing_id' => $viewingId]
        );
    }

    public static function viewingCancelled(int $userId, string $propertyTitle, int $viewingId, string $reason = '', bool $byRenter = true): void
    {
        $by = $byRenter ? 'You cancelled' : 'The landlord cancelled';
        self::create($userId, 'viewing_cancelled',
            'Viewing cancelled',
            "{$by} the viewing for \"{$propertyTitle}\"." . ($reason ? " Reason: {$reason}" : ''),
            ['viewing_id' => $viewingId]
        );
    }

    public static function paymentSuccessful(int $userId, string $purpose, float $amount, int $paymentId): void
    {
        self::create($userId, 'payment_successful',
            'Payment successful',
            "Your payment of GH₵" . number_format($amount, 2) . " for {$purpose} was successful.",
            ['payment_id' => $paymentId]
        );
    }

    public static function paymentFailed(int $userId, string $purpose, float $amount, int $paymentId): void
    {
        self::create($userId, 'payment_failed',
            'Payment failed',
            "Your payment of GH₵" . number_format($amount, 2) . " for {$purpose} failed. Please try again.",
            ['payment_id' => $paymentId]
        );
    }
}
