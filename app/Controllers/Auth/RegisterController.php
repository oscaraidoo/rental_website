<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\{UserModel, RenterModel};
use App\Helpers\{Security, Request};
use App\Helpers\Flash;
use App\Services\{EmailService, AuditService};

class RegisterController extends BaseController
{
    public function showRegister(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'super_admin' ? 'admin/dashboard' : 'renter/dashboard');
        }
        $this->view('auth.register', ['title' => 'Create Account'], null);
    }

    public function register(): void
    {
        Security::requireCsrf();

        $data = [
            'first_name' => Security::sanitize(Request::post('first_name', '')),
            'last_name'  => Security::sanitize(Request::post('last_name', '')),
            'email'      => Security::sanitizeEmail(Request::post('email', '')),
            'phone'      => Security::sanitize(Request::post('phone', '')),
            'password'   => Request::post('password', ''),
            'confirm'    => Request::post('confirm_password', ''),
            'role'       => Security::sanitize(Request::post('role', 'renter')),
            'terms'      => Request::post('agree_terms'),
        ];

        // Simplified signup card sends a single "full_name" field — split it here.
        $fullName = Security::sanitize(Request::post('full_name', ''));
        if ($fullName !== '' && $data['first_name'] === '' && $data['last_name'] === '') {
            $parts = preg_split('/\s+/', trim($fullName), 2);
            $data['first_name'] = $parts[0];
            $data['last_name']  = $parts[1] ?? $parts[0];
        }

        $errors = $this->validateRegistration($data);

        if (!empty($errors)) {
            $_SESSION['form_errors']  = $errors;
            $_SESSION['form_old']     = $data;
            $this->redirect('auth/register');
        }

        // Only allow renter registration from public form
        if (!in_array($data['role'], ['renter'], true)) {
            $data['role'] = 'renter';
        }

        $roleId = UserModel::getRoleIdBySlug($data['role']);
        if (!$roleId) {
            $this->error('Invalid account type selected.');
            $this->redirect('auth/register');
        }

        try {
            \App\Helpers\Database::beginTransaction();

            $userId = UserModel::create([
                'role_id'    => $roleId,
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'],
                'password'   => $data['password'],
                'status'     => 'pending_verification',
            ]);

            // Create extended profile — idempotent, so re-registering a
            // previously deleted account (same email) won't hit the unique
            // key on renters.user_id and fail.
            RenterModel::findOrCreateByUserId($userId);

            // Generate & send verification email
            $token = UserModel::createVerifyToken($userId);
            $sent  = EmailService::sendVerification($data['email'], $data['first_name'], $token);

            \App\Helpers\Database::commit();
        } catch (\Throwable $e) {
            \App\Helpers\Database::rollBack();
            error_log('Registration error: ' . $e->getMessage());
            $this->error('Registration failed. Please try again.');
            $this->redirect('auth/register');
        }

        if (!$sent) {
            // Account exists but the mail transport failed (e.g. SMTP not
            // configured yet) — tell the user instead of a silent dead end.
            error_log('Verification email could not be sent after registration for user ' . $userId);
            Flash::warning('Your account was created, but the verification email could not be sent right now. Please use the resend option in a few minutes.');
            $_SESSION['verify_email_hint'] = $data['email'];
            $this->redirect('auth/verify-email');
        }

        try {
            AuditService::log('register', 'auth', $userId, 'user', [], [], "New {$data['role']} registered");
        } catch (\Throwable $e) {
            // Audit logging must never break the registration response —
            // the account and verification email are already committed.
            error_log('Audit log failed after registration: ' . $e->getMessage());
        }

        $this->success('Account created! Please check your email to verify your address.');
        $_SESSION['verify_email_hint'] = $data['email'];
        $this->redirect('auth/verify-email');
    }

    public function showVerify(): void
    {
        $this->view('auth.verify_email', ['title' => 'Verify Email'], null);
    }

    public function verifyOtp(): void
    {
        Security::requireCsrf();

        // Collect the 6 individual digit fields and join them
        $digits = '';
        for ($i = 1; $i <= 6; $i++) {
            $digits .= preg_replace('/\D/', '', Request::post('otp' . $i, ''));
        }
        // Also accept a single otp field (resend form / paste)
        if (strlen($digits) < 6) {
            $digits = preg_replace('/\D/', '', Request::post('otp', ''));
        }
        $otp = substr($digits, 0, 6);

        if (strlen($otp) !== 6) {
            $this->error('Please enter the complete 6-digit code.');
            $this->redirect('auth/verify-email');
        }

        // Rate-limit OTP attempts per IP — max 5 tries per 10 minutes
        $rlKey = Security::getIp() . ':otp_verify';
        if (Security::isRateLimited($rlKey, 5, 10)) {
            $this->error('Too many attempts. Please wait 10 minutes or request a new code.');
            $this->redirect('auth/verify-email');
        }

        $record = UserModel::findVerifyToken($otp);

        if (!$record) {
            Security::recordAttempt($rlKey, 5, 10);
            $this->error('The code is incorrect or has expired. Please try again or request a new code.');
            $this->redirect('auth/verify-email');
        }

        Security::clearRateLimit($rlKey);
        UserModel::markEmailVerified((int) $record['user_id']);
        AuditService::log('email_verified', 'auth', (int) $record['user_id'], 'user');

        // Auto log in after successful verification
        $user = UserModel::findById((int) $record['user_id']);
        if ($user) {
            try { session_regenerate_id(true); } catch (\Throwable) {}
            $_SESSION['user_id']        = $user['id'];
            $_SESSION['user_role']      = $user['role_slug'];
            $_SESSION['user_name']      = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email']     = $user['email'];
            $_SESSION['user_avatar']    = $user['avatar'];
            $_SESSION['email_verified'] = true;

            $this->success('Email verified! Welcome to RentalWebsite, ' . $user['first_name'] . '.');
            $this->redirect('renter/dashboard');
        }

        $this->success('Email verified successfully. You can now log in.');
        $this->redirect('auth/login');
    }

    public function verifyEmail(string $token): void
    {
        // Legacy link-based verification (from old emails still in inboxes)
        $record = UserModel::findVerifyToken($token);

        if (!$record) {
            $this->error('This verification link is invalid or has expired.');
            $this->redirect('auth/verify-email');
        }

        UserModel::markEmailVerified((int) $record['user_id']);
        AuditService::log('email_verified', 'auth', (int) $record['user_id'], 'user');

        if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $record['user_id']) {
            $_SESSION['email_verified'] = true;
        }

        $this->success('Email verified successfully. You can now log in.');
        $this->redirect('auth/login');
    }

    public function resendVerification(): void
    {
        Security::requireCsrf();

        $email = Security::sanitizeEmail(Request::post('email', ''));
        if (empty($email)) {
            $this->error('Please enter your email address.');
            $this->redirect('auth/verify-email');
        }

        // Rate limit resends — max 3 per 5 minutes per IP
        $rlKey = Security::getIp() . ':resend_otp';
        if (Security::isRateLimited($rlKey, 3, 5)) {
            $this->error('Please wait a few minutes before requesting another code.');
            $this->redirect('auth/verify-email');
        }
        Security::recordAttempt($rlKey, 3, 5);

        $user = UserModel::findByEmail($email);
        if ($user && !$user['email_verified']) {
            $otp  = UserModel::createVerifyToken((int) $user['id']);
            EmailService::sendVerification($user['email'], $user['first_name'], $otp);
        }

        // Preserve email hint for the OTP page
        $_SESSION['verify_email_hint'] = $email;

        // Always show success to prevent email enumeration
        $this->success('A new 6-digit code has been sent. Please check your inbox.');
        $this->redirect('auth/verify-email');
    }

    // ── Validation ────────────────────────────────────────────

    private function validateRegistration(array $data): array
    {
        $errors   = [];
        $appCfg   = require ROOT_PATH . '/config/app.php';
        $minPass  = (int) ($appCfg['password_min_length'] ?? 8);

        if (empty($data['first_name']))
            $errors[] = 'First name is required.';
        elseif (strlen($data['first_name']) > 80)
            $errors[] = 'First name must be 80 characters or fewer.';

        if (empty($data['last_name']))
            $errors[] = 'Last name is required.';

        if (empty($data['email']))
            $errors[] = 'Email address is required.';
        elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
            $errors[] = 'Please enter a valid email address.';
        elseif (UserModel::existsByEmail($data['email']))
            $errors[] = 'An account with this email address already exists.';

        if (!empty($data['phone']) && !preg_match('/^\+?[\d\s\-()]{7,20}$/', $data['phone']))
            $errors[] = 'Please enter a valid phone number.';

        if (empty($data['password']))
            $errors[] = 'Password is required.';
        elseif (strlen($data['password']) < $minPass)
            $errors[] = "Password must be at least {$minPass} characters long.";
        elseif (!preg_match('/[A-Z]/', $data['password']))
            $errors[] = 'Password must contain at least one uppercase letter.';
        elseif (!preg_match('/[0-9]/', $data['password']))
            $errors[] = 'Password must contain at least one number.';

        if ($data['confirm'] !== '' && $data['password'] !== $data['confirm'])
            $errors[] = 'Passwords do not match.';

        if (empty($data['terms']))
            $errors[] = 'You must agree to the Terms and Conditions.';

        return $errors;
    }
}
