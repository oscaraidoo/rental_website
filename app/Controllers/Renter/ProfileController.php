<?php

declare(strict_types=1);

namespace App\Controllers\Renter;

use App\Controllers\BaseController;
use App\Models\{RenterModel, UserModel};
use App\Helpers\{Security, Request, Upload, Database};
use App\Services\{AuditService, NotificationService, EmailService};

class ProfileController extends BaseController
{
    // ── Shared data loader ────────────────────────────────────

    private function loadProfile(): array
    {
        $userId = $this->currentUserId();
        $renter = RenterModel::findOrCreateByUserId($userId);
        $user   = UserModel::findById($userId);
        return ['renter' => $renter, 'user' => $user, 'userId' => $userId];
    }

    // ── Profile completion score ──────────────────────────────

    public static function completionScore(array $u): array
    {
        $fields = [
            'first_name'   => ['label' => 'First name',       'weight' => 10],
            'last_name'    => ['label' => 'Last name',         'weight' => 10],
            'phone'        => ['label' => 'Phone number',      'weight' => 10],
            'avatar'       => ['label' => 'Profile photo',     'weight' => 15],
            'gender'       => ['label' => 'Gender',            'weight' => 5],
            'date_of_birth'=> ['label' => 'Date of birth',     'weight' => 5],
            'address'      => ['label' => 'Address',           'weight' => 10],
            'city'         => ['label' => 'City',              'weight' => 5],
            'email_verified'=> ['label'=> 'Email verified',    'weight' => 15],
            'phone_verified'=> ['label'=> 'Phone verified',    'weight' => 10],
            'occupation'   => ['label' => 'Occupation',        'weight' => 5],
        ];

        $total    = array_sum(array_column($fields, 'weight'));
        $earned   = 0;
        $missing  = [];

        foreach ($fields as $key => $cfg) {
            $val = $u[$key] ?? null;
            $done = match($key) {
                'email_verified', 'phone_verified' => (bool)(int)($val ?? 0),
                default                             => !empty($val),
            };
            if ($done) {
                $earned += $cfg['weight'];
            } else {
                $missing[] = $cfg['label'];
            }
        }

        $pct = $total > 0 ? (int) round(($earned / $total) * 100) : 0;
        return ['percent' => $pct, 'missing' => $missing];
    }

    // ══════════════════════════════════════════════════════════
    // GET /renter/profile
    // ══════════════════════════════════════════════════════════

    public function show(): void
    {
        $this->renterOnly();
        ['renter' => $renter] = $this->loadProfile();
        $completion = self::completionScore($renter);

        $this->renterView('renter.profile.index', [
            'renter'     => $renter,
            'completion' => $completion,
            'tab'        => 'profile',
            'title'      => 'My Profile',
            'noPageHead' => true,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // POST /renter/profile  — update personal info
    // ══════════════════════════════════════════════════════════

    public function update(): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $userId = $this->currentUserId();
        RenterModel::findOrCreateByUserId($userId);

        $firstName = Security::sanitize(Request::post('first_name', ''));
        $lastName  = Security::sanitize(Request::post('last_name', ''));
        $phone     = Security::sanitize(Request::post('phone', ''));
        $gender    = Request::post('gender', '');
        $dob       = Security::sanitize(Request::post('date_of_birth', '')) ?: null;
        $address   = Security::sanitize(Request::post('address', '')) ?: null;
        $region    = Security::sanitize(Request::post('region', '')) ?: null;
        $city      = Security::sanitize(Request::post('city', '')) ?: null;

        if (empty($firstName) || empty($lastName)) {
            $this->error('First and last name are required.');
            $this->redirect('renter/profile');
        }

        if (!in_array($gender, ['male','female','other','prefer_not',''], true)) {
            $gender = '';
        }

        $old = UserModel::findById($userId);

        UserModel::update($userId, array_filter([
            'first_name'    => $firstName,
            'last_name'     => $lastName,
            'phone'         => $phone,
            'gender'        => $gender ?: null,
            'date_of_birth' => $dob,
            'address'       => $address,
            'region'        => $region,
            'city'          => $city,
        ], fn($v) => $v !== null));

        RenterModel::update($userId, [
            'occupation'           => Security::sanitize(Request::post('occupation', '')),
            'employer'             => Security::sanitize(Request::post('employer', '')),
            'monthly_income_range' => Security::sanitize(Request::post('monthly_income_range', '')),
            'num_occupants'        => max(1, (int) Request::post('num_occupants', 1)),
            'has_pets'             => (int) !empty(Request::post('has_pets')),
            'pet_description'      => Security::sanitize(Request::post('pet_description', '')),
            'budget_min'           => !empty(Request::post('budget_min')) ? (float) Request::post('budget_min') : null,
            'budget_max'           => !empty(Request::post('budget_max')) ? (float) Request::post('budget_max') : null,
            'preferred_move_in'    => Security::sanitize(Request::post('preferred_move_in', '')) ?: null,
        ]);

        $_SESSION['user_name'] = $firstName . ' ' . $lastName;

        AuditService::log('profile_updated', 'users', $userId, 'user',
            ['first_name' => $old['first_name'], 'last_name' => $old['last_name']],
            ['first_name' => $firstName, 'last_name' => $lastName],
            'User updated profile information'
        );

        if (Request::isAjax()) {
            $this->jsonSuccess(['name' => $firstName . ' ' . $lastName], 'Profile updated.');
        }

        $this->success('Profile updated successfully.');
        $this->redirect('renter/profile');
    }

    // ══════════════════════════════════════════════════════════
    // POST /renter/profile/photo  — upload avatar
    // ══════════════════════════════════════════════════════════

    public function updatePhoto(): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $userId = $this->currentUserId();
        $file   = $_FILES['avatar'] ?? null;

        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            if (Request::isAjax()) $this->jsonError('No file received.');
            $this->error('No file received.');
            $this->redirect('renter/profile');
        }

        // Extra MIME validation
        $allowedMimes = ['image/jpeg','image/png','image/webp'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name'] ?? '');
        if (!in_array($mime, $allowedMimes, true)) {
            if (Request::isAjax()) $this->jsonError('Only JPG, PNG and WEBP images are allowed.');
            $this->error('Only JPG, PNG and WEBP images are allowed.');
            $this->redirect('renter/profile');
        }

        if (($file['size'] ?? 0) > 3 * 1024 * 1024) {
            if (Request::isAjax()) $this->jsonError('Image must be under 3 MB.');
            $this->error('Image must be under 3 MB.');
            $this->redirect('renter/profile');
        }

        $result = Upload::handle($file, 'image', 'avatars');
        if (!$result['success']) {
            if (Request::isAjax()) $this->jsonError($result['error'] ?? 'Upload failed.');
            $this->error($result['error'] ?? 'Upload failed.');
            $this->redirect('renter/profile');
        }

        // Delete old avatar
        $old = UserModel::findById($userId);
        if (!empty($old['avatar'])) {
            Upload::delete($old['avatar']);
        }

        UserModel::update($userId, ['avatar' => $result['path']]);
        $_SESSION['user_avatar'] = $result['path'];

        AuditService::log('profile_photo_changed', 'users', $userId, 'user');

        if (Request::isAjax()) {
            $this->jsonSuccess([
                'path' => \App\Helpers\Url::image($result['path']),
            ], 'Photo updated.');
        }

        $this->success('Profile photo updated.');
        $this->redirect('renter/profile');
    }

    // ══════════════════════════════════════════════════════════
    // POST /renter/profile/photo/remove
    // ══════════════════════════════════════════════════════════

    public function removePhoto(): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $userId = $this->currentUserId();
        $user   = UserModel::findById($userId);

        if (!empty($user['avatar'])) {
            Upload::delete($user['avatar']);
            UserModel::update($userId, ['avatar' => null]);
            $_SESSION['user_avatar'] = null;
            AuditService::log('profile_photo_removed', 'users', $userId, 'user');
        }

        if (Request::isAjax()) $this->jsonSuccess(null, 'Photo removed.');
        $this->success('Profile photo removed.');
        $this->redirect('renter/profile');
    }

    // ══════════════════════════════════════════════════════════
    // GET /renter/profile/security
    // ══════════════════════════════════════════════════════════

    public function security(): void
    {
        $this->renterOnly();
        ['renter' => $renter, 'userId' => $userId] = $this->loadProfile();

        // Recent audit activity for this user
        $activity = Database::fetchAll(
            "SELECT action, description, ip_address, created_at
             FROM audit_logs
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT 20",
            [$userId]
        );

        $this->renterView('renter.profile.security', [
            'renter'     => $renter,
            'activity'   => $activity,
            'tab'        => 'security',
            'title'      => 'Security',
            'noPageHead' => true,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // POST /renter/profile/password
    // ══════════════════════════════════════════════════════════

    public function changePassword(): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $userId  = $this->currentUserId();
        $user    = UserModel::findById($userId);
        $current = Request::post('current_password', '');
        $new     = Request::post('new_password', '');
        $confirm = Request::post('confirm_password', '');

        $isAjax = Request::isAjax();

        if (!Security::verifyPassword($current, $user['password'])) {
            if ($isAjax) $this->jsonError('Current password is incorrect.');
            $this->error('Current password is incorrect.');
            $this->redirect('renter/profile/security');
        }
        if (strlen($new) < 8) {
            if ($isAjax) $this->jsonError('Password must be at least 8 characters.');
            $this->error('Password must be at least 8 characters.');
            $this->redirect('renter/profile/security');
        }
        if (!preg_match('/[A-Z]/', $new)) {
            if ($isAjax) $this->jsonError('Password must contain at least one uppercase letter.');
            $this->error('Password must contain at least one uppercase letter.');
            $this->redirect('renter/profile/security');
        }
        if (!preg_match('/[0-9]/', $new)) {
            if ($isAjax) $this->jsonError('Password must contain at least one number.');
            $this->error('Password must contain at least one number.');
            $this->redirect('renter/profile/security');
        }
        if ($new !== $confirm) {
            if ($isAjax) $this->jsonError('Passwords do not match.');
            $this->error('Passwords do not match.');
            $this->redirect('renter/profile/security');
        }

        UserModel::update($userId, ['password' => $new]);

        AuditService::log('password_changed', 'users', $userId, 'user', [], [], 'User changed their password');

        // Email notification
        try {
            EmailService::send(
                $user['email'],
                'Your password was changed — RentalWebsite',
                '<p>Hi ' . htmlspecialchars($user['first_name']) . ',</p><p>Your password was changed successfully. If you did not make this change, please contact support immediately.</p>'
            );
        } catch (\Throwable) {}

        if ($isAjax) $this->jsonSuccess(null, 'Password changed successfully.');
        $this->success('Password changed successfully.');
        $this->redirect('renter/profile/security');
    }

    // ══════════════════════════════════════════════════════════
    // GET /renter/profile/settings
    // ══════════════════════════════════════════════════════════

    public function settings(): void
    {
        $this->renterOnly();
        ['renter' => $renter, 'userId' => $userId] = $this->loadProfile();

        $prefs = [];
        if (!empty($renter['notification_prefs'])) {
            $prefs = json_decode($renter['notification_prefs'], true) ?? [];
        }

        $this->renterView('renter.profile.settings', [
            'renter'     => $renter,
            'prefs'      => $prefs,
            'tab'        => 'settings',
            'title'      => 'Settings',
            'noPageHead' => true,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // POST /renter/profile/settings
    // ══════════════════════════════════════════════════════════

    public function saveSettings(): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $userId = $this->currentUserId();
        $prefs  = [
            'email_notifications' => !empty(Request::post('email_notifications')),
            'sms_notifications'   => !empty(Request::post('sms_notifications')),
            'inapp_notifications' => !empty(Request::post('inapp_notifications')),
            'marketing_emails'    => !empty(Request::post('marketing_emails')),
            'viewing_reminders'   => !empty(Request::post('viewing_reminders')),
            'property_alerts'     => !empty(Request::post('property_alerts')),
        ];

        UserModel::update($userId, ['notification_prefs' => json_encode($prefs)]);
        AuditService::log('settings_updated', 'users', $userId, 'user');

        if (Request::isAjax()) $this->jsonSuccess($prefs, 'Settings saved.');
        $this->success('Settings saved successfully.');
        $this->redirect('renter/profile/settings');
    }

    // ══════════════════════════════════════════════════════════
    // GET /renter/profile/account
    // ══════════════════════════════════════════════════════════

    public function account(): void
    {
        $this->renterOnly();
        ['renter' => $renter] = $this->loadProfile();

        $this->renterView('renter.profile.account', [
            'renter'     => $renter,
            'tab'        => 'account',
            'title'      => 'Account',
            'noPageHead' => true,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // POST /renter/profile/deactivate
    // ══════════════════════════════════════════════════════════

    public function deactivate(): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $userId   = $this->currentUserId();
        $user     = UserModel::findById($userId);
        $password = Request::post('password', '');
        $reason   = Security::sanitize(Request::post('reason', ''));

        if (!Security::verifyPassword($password, $user['password'])) {
            $this->error('Password is incorrect. Deactivation cancelled.');
            $this->redirect('renter/profile/account');
        }

        UserModel::update($userId, ['status' => 'inactive']);
        AuditService::log('account_deactivated', 'users', $userId, 'user', [], ['reason' => $reason], 'User deactivated their account');

        // Destroy session
        session_destroy();
        session_start();
        $this->success('Your account has been deactivated. You can reactivate it by contacting support.');
        $this->redirect('auth/login');
    }

    // ══════════════════════════════════════════════════════════
    // POST /renter/profile/resend-verification
    // ══════════════════════════════════════════════════════════

    public function resendVerification(): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $userId = $this->currentUserId();
        $user   = UserModel::findById($userId);

        if (!empty($user['email_verified'])) {
            if (Request::isAjax()) $this->jsonError('Email is already verified.');
            $this->warning('Your email is already verified.');
            $this->redirect('renter/profile');
        }

        // Rate limit: 1 per 5 minutes
        $rlKey = Security::getIp() . ':resend_verification';
        if (Security::isRateLimited($rlKey, 3, 5)) {
            if (Request::isAjax()) $this->jsonError('Please wait before requesting another verification email.');
            $this->error('Please wait a few minutes before requesting another verification email.');
            $this->redirect('renter/profile');
        }
        Security::recordAttempt($rlKey, 3, 5);

        $token = UserModel::createVerifyToken($userId);
        try {
            EmailService::sendVerification($user['email'], $user['first_name'], $token);
        } catch (\Throwable) {}

        if (Request::isAjax()) $this->jsonSuccess(null, 'Verification email sent.');
        $this->success('Verification email sent. Please check your inbox.');
        $this->redirect('renter/profile');
    }
}
