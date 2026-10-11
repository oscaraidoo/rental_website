<?php

declare(strict_types=1);

namespace App\Controllers\Renter;

use App\Controllers\NotificationBaseController;
use App\Helpers\{Security, Request};
use App\Models\UserModel;
use App\Services\{NotificationService, NotificationTemplates};

class NotificationController extends NotificationBaseController
{
    protected function authOnly(): void   { $this->renterOnly(); }
    protected function routePrefix(): string { return 'renter/notifications'; }
    protected function viewName(): string { return 'renter.notifications.index'; }
    protected function pageTitle(): string { return 'Notifications'; }

    protected function viewWith(array $data): void
    {
        $this->renterView($this->viewName(), $data);
    }

    /** GET /renter/notifications/settings — channel + type preferences page. */
    public function settings(): void
    {
        $this->renterOnly();
        $userId = (int) $this->currentUserId();

        $catalogue = [];
        foreach (NotificationTemplates::TEMPLATES as $type => $def) {
            $catalogue[$def['group']][] = [
                'type'        => $type,
                'label'       => $def['label'],
                'channels'    => $def['channels'],
            ];
        }

        $this->renterView('renter.notifications.settings', [
            'prefs'     => NotificationService::preferences($userId),
            'user'      => UserModel::findById($userId),
            'catalogue' => $catalogue,
            'title'     => 'Notification Settings',
        ]);
    }

    /** POST /renter/notifications/settings — save preferences (form fallback to the API). */
    public function saveSettings(): void
    {
        $this->renterOnly();
        Security::requireCsrf();
        $userId = (int) $this->currentUserId();

        $muted = [];
        foreach (NotificationTemplates::TEMPLATES as $type => $def) {
            if (Request::post('muted_' . $type) !== '1') {
                $muted[] = $type; // unchecked = muted
            }
        }

        $phoneRaw = trim((string) Request::post('phone_local', ''));
        $patch = [
            'email_enabled' => Request::post('email_enabled') === 'on',
            'sms_enabled'   => Request::post('sms_enabled') === 'on',
            'muted_types'   => $muted,
        ];
        if ($phoneRaw !== '') {
            // Form field is local-part; full international also accepted.
            $patch['phone'] = str_starts_with($phoneRaw, '+') ? $phoneRaw : '+233' . preg_replace('/\D/', '', $phoneRaw);
        }

        $res = NotificationService::updatePreferences($userId, $patch);
        if (!$res['ok']) {
            $this->error($res['error']);
        } else {
            $this->success('Notification settings saved.');
        }
        $this->redirect('renter/notifications/settings');
    }
}
