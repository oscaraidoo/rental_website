<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\{UserModel, RenterModel};
use App\Helpers\{Security, Request};
use App\Services\AuditService;

class LoginController extends BaseController
{
    public function showLogin(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirectToDashboard($_SESSION['user_role'] ?? '');
        }
        // Auth views are standalone — pass null layout so they render as-is
        $this->view('auth.login', ['title' => 'Login'], null);
    }

    public function login(): void
    {
        Security::requireCsrf();

        $email    = Security::sanitizeEmail(Request::post('email', ''));
        $password = Request::post('password', '');
        $remember = !empty(Request::post('remember_me'));

        // Basic validation
        if (empty($email) || empty($password)) {
            $this->error('Email and password are required.');
            $this->redirect('auth/login');
        }

        // Rate limiting — per IP
        $rlKey = Security::getIp() . ':login';
        $appCfg = require ROOT_PATH . '/config/app.php';
        $maxAttempts  = (int) ($appCfg['login_max_attempts'] ?? 5);
        $lockMinutes  = (int) ($appCfg['login_lockout_minutes'] ?? 30);

        if (Security::isRateLimited($rlKey, $maxAttempts, $lockMinutes)) {
            $this->error("Too many failed attempts. Please try again in {$lockMinutes} minutes.");
            $this->redirect('auth/login');
        }

        $user = UserModel::findByEmail($email);

        if (!$user || !Security::verifyPassword($password, $user['password'])) {
            Security::recordAttempt($rlKey, $maxAttempts, $lockMinutes);
            AuditService::log('login_failed', 'auth', null, '', [], [], "Failed login for: {$email}");
            $this->error('Invalid email or password.');
            $this->redirect('auth/login');
        }

        // Check account status
        if ($user['status'] === 'suspended') {
            $this->error('Your account has been suspended. Please contact support.');
            $this->redirect('auth/login');
        }

        if ($user['status'] === 'pending_verification') {
            // Allow login but warn about email verification
            $this->warning('Your email address is not yet verified. Some features may be limited.');
        }

        // Check if locked
        if (UserModel::isLocked($user)) {
            $this->error("Account temporarily locked due to too many failed attempts. Please try again later.");
            $this->redirect('auth/login');
        }

        // Rehash password if needed (wrapped — bcrypt check can fail on some hosts)
        try {
            if (Security::needsRehash($user['password'])) {
                UserModel::update((int) $user['id'], ['password' => Security::hashPassword($password)]);
            }
        } catch (\Throwable) { /* non-fatal */ }

        // Record login
        UserModel::recordLogin((int) $user['id'], Security::getIp());
        Security::clearRateLimit($rlKey);

        // Set session
        $this->createUserSession($user);

        // Remember me
        if ($remember) {
            $token = Security::generateToken(32);
            UserModel::setRememberToken((int) $user['id'], $token);
            setcookie('remember_token', $token, [
                'expires'  => time() + (30 * 86400),
                'path'     => '/',
                'httponly' => true,
                'secure'   => ($_ENV['APP_ENV'] ?? 'development') !== 'development',
                'samesite' => 'Lax',
            ]);
        }

        AuditService::log('login', 'auth', (int) $user['id'], 'user', [], [], "User logged in");

        $this->success('Welcome back, ' . $user['first_name'] . '!');
        $this->redirectToDashboard($user['role_slug']);
    }

    public function logout(): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        // Clear remember me cookie
        if (!empty($_COOKIE['remember_token'])) {
            if ($userId) UserModel::setRememberToken((int) $userId, '');
            setcookie('remember_token', '', time() - 3600, '/');
        }

        AuditService::log('logout', 'auth', $userId, 'user');

        session_destroy();
        session_start();

        $this->success('You have been logged out.');
        $this->redirect('auth/login');
    }

    // ── Helpers ──────────────────────────────────────────────

    private function createUserSession(array $user): void
    {
        // session_regenerate_id can fail on some shared hosts — wrap it
        try { session_regenerate_id(true); } catch (\Throwable) { }
        $_SESSION['user_id']        = $user['id'];
        $_SESSION['user_role']      = $user['role_slug'];
        $_SESSION['user_name']      = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email']     = $user['email'];
        $_SESSION['user_avatar']    = $user['avatar'];
        $_SESSION['email_verified'] = (bool) $user['email_verified'];
    }

    private function redirectToDashboard(string $role): never
    {
        match ($role) {
            'super_admin', 'admin' => $this->redirect('admin/dashboard'),
            'renter'               => $this->redirect('renter/dashboard'),
            default                => $this->redirect(''),
        };
    }
}
