<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SettingsModel;
use App\Helpers\{Security, Request};
use App\Services\AuditService;

class SettingsController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();
        $settings = SettingsModel::all();

        // Server-side tabs: ?tab=email etc. Falls back to the first group.
        // (The previous Bootstrap-JS tabs never switched panes — the plugin
        // threw "Illegal invocation" on this custom nav markup — so "Email"
        // appeared permanently stuck as the only visible section.)
        $groups      = array_keys($settings);
        $requested   = Security::sanitize(Request::get('tab', ''));
        $activeGroup = in_array($requested, $groups, true) ? $requested : ($groups[0] ?? 'general');

        $this->adminView('admin.settings.index',
            compact('settings', 'activeGroup') + [
                'title' => 'System Settings', 'breadcrumb' => ['Settings'],
            ]);
    }

    public function update(): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $group    = Security::sanitize(Request::post('group', 'general'));
        $settings = SettingsModel::getGroup($group);
        $updated  = [];

        foreach ($settings as $key => $row) {
            $val = Request::post($key);
            if ($val === null) continue;
            $sanitized = Security::sanitize(is_array($val) ? implode(',', $val) : $val);
            SettingsModel::set($key, $sanitized);
            $updated[$key] = $sanitized;
        }

        AuditService::log('update_settings', 'settings', null, '', [], $updated, "Updated {$group} settings");
        $this->success('Settings saved successfully.');
        // Return to the tab the user was on instead of snapping back to Email.
        $this->redirect('admin/settings?tab=' . urlencode($group));
    }

    public function fees(): void
    {
        $this->adminOnly();
        $settings = SettingsModel::getGroup('fees');
        $this->adminView('admin.settings.fees', compact('settings') + [
            'title' => 'Fee Configuration', 'breadcrumb' => ['Settings', 'Fees'],
        ]);
    }

    public function updateFees(): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $feeKeys = [
            'commission_rate', 'commission_landlord_rate', 'commission_renter_rate',
            'viewing_fee_default', 'registration_fee', 'verification_fee', 'featured_listing_fee',
            'landlord_contact_fee',
        ];

        foreach ($feeKeys as $key) {
            $val = (float) Request::post($key, 0);
            if ($val < 0) $val = 0;
            SettingsModel::set($key, (string) $val);
        }

        AuditService::log('update_fees', 'settings', null, '', [], [], 'Admin updated fee configuration');
        $this->success('Fee configuration updated.');
        $this->redirect('admin/settings/fees');
    }
}
