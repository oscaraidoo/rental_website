<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Helpers\{Security, Request};
use App\Services\{EmailService, AuditService};

class PasswordController extends BaseController
{
    public function showForgot(): void
    {
        // Null layout: the view is a complete standalone page (photo background,
        // brand, card) matching the sign-in/sign-up design language. Wrapping it
        // in layouts/auth would double-render the document.
        $this->view('auth.forgot_password', ['title' => 'Forgot Password'], null);
    }

    public function sendReset(): void
    {
        Security::requireCsrf();

        $email = Security::sanitizeEmail(Request::post('email', ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Please enter a valid email address.');
            $this->redirect('auth/forgot-password');
        }

        // Rate limit: max 3 reset requests per IP per hour
        $rlKey = Security::getIp() . ':password_reset';
        if (Security::isRateLimited($rlKey, 3, 60)) {
            $this->error('Too many requests. Please wait before trying again.');
            $this->redirect('auth/forgot-password');
        }
        Security::recordAttempt($rlKey, 3, 60);

        $user = UserModel::findByEmail($email);

        if ($user && $user['status'] !== 'suspended') {
            $token = UserModel::createPasswordReset($email);
            EmailService::sendPasswordReset($user['email'], $user['first_name'], $token);
            AuditService::log('password_reset_requested', 'auth', (int) $user['id'], 'user');
        }

        // Always show same message to prevent email enumeration
        $this->success('If that email is registered, a password reset link has been sent.');
        $this->redirect('auth/forgot-password');
    }

    public function showReset(string $token): void
    {
        $record = UserModel::findPasswordReset($token);
        if (!$record) {
            $this->error('This password reset link is invalid or has expired.');
            $this->redirect('auth/forgot-password');
        }
        $this->view('auth.reset_password', ['title' => 'Reset Password', 'token' => $token], null);
    }

    public function resetPassword(): void
    {
        Security::requireCsrf();

        $token    = Security::sanitize(Request::post('token', ''));
        $password = Request::post('password', '');
        $confirm  = Request::post('confirm_password', '');

        $record = UserModel::findPasswordReset($token);
        if (!$record) {
            $this->error('This password reset link is invalid or has expired.');
            $this->redirect('auth/forgot-password');
        }

        $appCfg  = require ROOT_PATH . '/config/app.php';
        $minPass = (int) ($appCfg['password_min_length'] ?? 8);
        $errors  = [];

        if (strlen($password) < $minPass)
            $errors[] = "Password must be at least {$minPass} characters.";
        if (!preg_match('/[A-Z]/', $password))
            $errors[] = 'Password must contain at least one uppercase letter.';
        if (!preg_match('/[0-9]/', $password))
            $errors[] = 'Password must contain at least one number.';
        if ($password !== $confirm)
            $errors[] = 'Passwords do not match.';

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $this->redirect('auth/reset-password/' . $token);
        }

        $user = UserModel::findByEmail($record['email']);
        if (!$user) {
            $this->error('Account not found.');
            $this->redirect('auth/login');
        }

        // Clear any "remember me" token so a stolen session cannot survive
        // a password change.
        UserModel::update((int) $user['id'], ['password' => $password, 'remember_token' => null]);
        UserModel::consumePasswordReset($token);
        AuditService::log('password_reset', 'auth', (int) $user['id'], 'user');

        $this->success('Password reset successfully. Please log in with your new password.');
        $this->redirect('auth/login');
    }
}
