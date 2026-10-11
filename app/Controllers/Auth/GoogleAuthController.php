<?php
/**
 * Google OAuth Controller
 * Sign-up / sign-in via Google (OpenID Connect).
 *
 * Flow:
 *   1. redirect()  — issue state, bounce to Google's consent screen
 *   2. callback()  — verify state, exchange code, then:
 *        a. google_id match        → log in
 *        b. same email, local user → link account, log in
 *        c. no match               → auto-register as renter
 *                                    (email considered verified), log in
 */

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Helpers\{GoogleOAuth, Security, Request};
use App\Models\{UserModel, RenterModel};
use App\Services\{AuditService, EmailService};

class GoogleAuthController extends BaseController
{
    /** Step 1 — send the user to Google's consent screen. */
    public function redirectToGoogle(): never
    {
        if (!GoogleOAuth::isEnabled()) {
            $this->error('Google sign-in is not configured on this server.');
            $this->redirect('auth/login');
        }

        if (!empty($_SESSION['user_id'])) {
            $this->redirectLoggedInUser();
        }

        $state = GoogleOAuth::issueState();
        header('Location: ' . GoogleOAuth::authUrl($state), true, 302);
        exit;
    }

    /** Step 2 — handle Google's response. */
    public function callback(): void
    {
        // User pressed "Cancel" on the consent screen, or Google bounced with an error
        if (!empty(Request::get('error'))) {
            $this->error('Google sign-in was cancelled. You can use email and password instead.');
            $this->redirect('auth/login');
        }

        if (!GoogleOAuth::verifyState(Request::get('state'))) {
            $this->error('Your Google sign-in session expired. Please try again.');
            $this->redirect('auth/login');
        }

        $code = (string) Request::get('code', '');
        if ($code === '') {
            $this->error('Google sign-in failed. Please try again.');
            $this->redirect('auth/login');
        }

        try {
            $g = GoogleOAuth::fetchUser($code);
        } catch (\Throwable $e) {
            error_log('Google OAuth: ' . $e->getMessage());
            $this->error('Could not complete Google sign-in. Please try again.');
            $this->redirect('auth/login');
        }

        // Google only returns email_verified accounts for the email scope;
        // refuse otherwise (prevents account takeover via unverified mail).
        if (!$g['email_verified']) {
            $this->error('Your Google account email is not verified, so we cannot use it to sign in.');
            $this->redirect('auth/login');
        }

        try {
            // (a) Already linked → plain login
            $user = UserModel::findByGoogleId($g['sub']);

            // (b) Existing local account with the same email → link and log in
            if (!$user) {
                $user = UserModel::findByEmail($g['email']);
                if ($user) {
                    if ($user['status'] === 'suspended') {
                        $this->error('Your account has been suspended. Please contact support.');
                        $this->redirect('auth/login');
                    }
                    UserModel::linkGoogle((int) $user['id'], $g['sub']);
                    AuditService::log('google_account_linked', 'auth', (int) $user['id'], 'user', [], [], "Google account linked for {$g['email']}");
                    $this->success('Your Google account is now linked. Welcome back!');
                }
            }

            // (c) No account at all → auto-register with the chosen role
            if (!$user) {
                $userId = $this->registerGoogleUser($g);
                $user = UserModel::findById($userId);
                AuditService::log('register_google', 'auth', $userId, 'user', [], [], "New renter registered via Google");
            } else {
                $userId = (int) $user['id'];
            }
        } catch (\Throwable $e) {
            error_log('Google OAuth DB: ' . $e->getMessage());
            $this->error('Could not complete Google sign-in. Please try again.');
            $this->redirect('auth/login');
        }

        if (!$user) {
            $this->error('Could not complete Google sign-in. Please try again.');
            $this->redirect('auth/login');
        }

        if ($user['status'] === 'suspended') {
            $this->error('Your account has been suspended. Please contact support.');
            $this->redirect('auth/login');
        }

        // Google users skip the pending_verification gate: Google verified the email
        if ($user['status'] === 'pending_verification') {
            UserModel::markEmailVerified($userId);
            $user = UserModel::findById($userId) ?? $user;
        }

        UserModel::recordLogin($userId, Security::getIp());
        $this->createUserSession($user);
        AuditService::log('login_google', 'auth', $userId, 'user', [], [], 'User logged in via Google');

        $this->success('Welcome, ' . $user['first_name'] . '!');
        $this->redirectLoggedInUser($user['role_slug'] ?? '');
    }

    // ── Helpers ──────────────────────────────────────────────

    private function registerGoogleUser(array $g): int
    {
        $role   = 'renter';
        $roleId = UserModel::getRoleIdBySlug($role);
        if (!$roleId) {
            throw new \RuntimeException('Invalid role configuration.');
        }

        $firstName = $g['given_name'] !== '' ? $g['given_name'] : ($g['name'] !== '' ? explode(' ', trim($g['name']))[0] : 'User');
        $lastName  = $g['family_name'] !== ''
            ? $g['family_name']
            : (trim($g['name']) !== '' && str_contains(trim($g['name']), ' ')
                ? substr(strstr(trim($g['name']), ' '), 1)
                : 'Google');

        \App\Helpers\Database::beginTransaction();
        try {
            $userId = UserModel::create([
                'role_id'    => $roleId,
                'first_name' => Security::sanitize($firstName),
                'last_name'  => Security::sanitize($lastName),
                'email'      => $g['email'],
                'phone'      => '',
                'password'   => Security::generateToken(32), // random; hashed by UserModel::create
                'status'     => 'active', // Google verified the email — skip the pending gate
                'email_verified' => 1,
                'auth_provider'  => 'google',
            ]);

            // Idempotent: handles resurrected (soft-deleted) accounts and
            // concurrent requests, mirroring RegisterController behaviour.
            RenterModel::findOrCreateByUserId($userId);

            \App\Helpers\Database::commit();
        } catch (\Throwable $e) {
            \App\Helpers\database::rollBack();
            throw $e;
        }

        // Notify the user with a password-reset link so they can also use email+password
        try {
            $token = UserModel::createPasswordReset($g['email']);
            EmailService::sendPasswordReset($g['email'], $firstName, $token);
        } catch (\Throwable $e) {
            error_log('Google signup email failed: ' . $e->getMessage());
        }

        return $userId;
    }

    private function createUserSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']        = $user['id'];
        $_SESSION['user_role']      = $user['role_slug'];
        $_SESSION['user_name']      = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email']     = $user['email'];
        $_SESSION['user_avatar']    = $user['avatar'];
        $_SESSION['email_verified'] = (bool) $user['email_verified'];
    }

    private function redirectLoggedInUser(string $role = ''): never
    {
        $role = $role !== '' ? $role : ($_SESSION['user_role'] ?? '');
        match ($role) {
            'super_admin', 'admin' => $this->redirect('admin/dashboard'),
            'renter'               => $this->redirect('renter/dashboard'),
            default                => $this->redirect(''),
        };
    }
}
