<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Helpers\{Request, Security};
use App\Services\{NotificationService, NotificationTemplates, SmsProvider, EmailProvider};

/**
 * Notification settings + test + history API.
 * All endpoints require an authenticated session (any role).
 * Test endpoints are strictly rate-limited and can only target the
 * current user's own addresses — no arbitrary sends are possible.
 */
class NotificationSettingsApiController extends BaseController
{
    /** GET /api/notification-settings */
    public function show(): void
    {
        $userId = (int) $this->currentUserId();
        $prefs  = NotificationService::preferences($userId);
        $user   = \App\Models\UserModel::findById($userId);

        $this->json([
            'success' => true,
            'data' => [
                'email'            => $user['email'] ?? null,
                'phone'            => $prefs['phone_e164'],
                'email_enabled'    => $prefs['email_enabled'],
                'sms_enabled'      => $prefs['sms_enabled'],
                'muted_types'      => $prefs['muted_types'],
                'available_types'  => $this->typeCatalogue(),
                'providers'        => ['email' => EmailProvider::driverName(), 'sms' => SmsProvider::driverName()],
            ],
        ]);
    }

    /** POST /api/notification-settings (CSRF-protected) */
    public function update(): void
    {
        $userId = (int) $this->currentUserId();
        Security::requireCsrf();

        $patch = [];
        if (Request::post('email_enabled') !== null)  $patch['email_enabled'] = Request::post('email_enabled') === '1' || Request::post('email_enabled') === 'true' || Request::post('email_enabled') === 'on';
        if (Request::post('sms_enabled') !== null)    $patch['sms_enabled']   = Request::post('sms_enabled') === '1' || Request::post('sms_enabled') === 'true' || Request::post('sms_enabled') === 'on';
        if (Request::post('phone') !== null)          $patch['phone']         = trim((string) Request::post('phone'));
        if (Request::post('muted_types') !== null) {
            $raw = (string) Request::post('muted_types');
            $patch['muted_types'] = $raw === '' ? [] : array_map('trim', explode(',', $raw));
        }

        $res = NotificationService::updatePreferences($userId, $patch);
        if (!$res['ok']) {
            $this->jsonError($res['error'], 422);
        }

        $this->jsonSuccess(NotificationService::preferences($userId), 'Notification settings saved.');
    }

    /** POST /api/notification-settings/test — sends a TEST notification to the CURRENT user only. */
    public function test(): void
    {
        $userId = (int) $this->currentUserId();
        Security::requireCsrf();

        // Rate limit: 3 test sends per 10 minutes per user
        $key = 'notif_test_' . $userId;
        if (Security::isRateLimited($key, 3, 10)) {
            $this->jsonError('Too many test notifications. Try again later.', 429);
        }
        Security::recordAttempt($key, 3, 10);

        $channel = (string) Request::post('channel', 'email');
        if (!in_array($channel, ['email', 'sms'], true)) {
            $this->jsonError('Channel must be "email" or "sms".', 422);
        }

        $prefs = NotificationService::preferences($userId);
        if ($channel === 'sms') {
            if (empty($prefs['phone_e164'])) {
                $this->jsonError('Add and save a phone number before testing SMS.', 422);
            }
            if (!$prefs['sms_enabled']) {
                $this->jsonError('SMS notifications are turned off. Enable them first.', 422);
            }
        }

        $r = NotificationService::send(
            $userId,
            'ADMIN_ANNOUNCEMENT',
            ['title' => 'Test notification', 'message' => 'This is a test ' . strtoupper($channel) . ' notification from RentalWebsite. If you received this, your settings work.'],
            [$channel],
            'test_' . $channel . '_' . time()
        );

        $ok = $r[$channel] !== null;
        $this->json([
            'success' => $ok,
            'message' => $ok
                ? 'Test ' . strtoupper($channel) . ' queued/sent — check your ' . ($channel === 'email' ? 'inbox (and spam)' : 'phone') . '.'
                : 'Test ' . strtoupper($channel) . ' could not be sent (provider disabled or user prefs blocked it).',
        ], $ok ? 200 : 502);
    }

    /** GET /api/notification-history — the current user's own outbound log. */
    public function history(): void
    {
        $userId  = (int) $this->currentUserId();
        $limit   = min(50, max(1, (int) Request::get('limit', 20)));
        $offset  = max(0, (int) Request::get('offset', 0));

        $rows = NotificationService::history($limit, $offset, ['user_id' => $userId]);

        // Strip fields the user has no business seeing
        $rows = array_map(function ($r) {
            unset($r['idempotency_key'], $r['template']);
            return $r;
        }, $rows);

        $this->json([
            'success' => true,
            'data'    => $rows,
            'total'   => NotificationService::historyCount(['user_id' => $userId]),
        ]);
    }

    /** Type catalogue grouped for the settings UI. */
    private function typeCatalogue(): array
    {
        $out = [];
        foreach (NotificationTemplates::TEMPLATES as $type => $def) {
            $out[$def['group']][] = [
                'type'        => $type,
                'label'       => $def['label'],
                'description' => $def['description'],
                'channels'    => $def['channels'],
            ];
        }
        return $out;
    }
}
