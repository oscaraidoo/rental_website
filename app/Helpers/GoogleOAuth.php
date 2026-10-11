<?php
/**
 * Google OAuth 2.0 Helper
 * Plain cURL implementation — no external packages required.
 *
 * Enable in .env:
 *   GOOGLE_CLIENT_ID=xxxx.apps.googleusercontent.com
 *   GOOGLE_CLIENT_SECRET=GOCSPX-xxxx
 *
 * Redirect URI to register in Google Cloud Console:
 *   {APP_URL}/auth/google/callback
 */

declare(strict_types=1);

namespace App\Helpers;

class GoogleOAuth
{
    private const AUTH_URL     = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL    = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

    public static function isEnabled(): bool
    {
        $cfg = self::config();
        return $cfg['client_id'] !== '' && $cfg['client_secret'] !== '';
    }

    /**
     * Build the Google consent-screen URL.
     * The $state must be stored server-side (issueState does this) before redirecting.
     */
    public static function authUrl(string $state): string
    {
        $cfg    = self::config();
        $params = http_build_query([
            'client_id'              => $cfg['client_id'],
            'redirect_uri'           => $cfg['redirect_uri'],
            'response_type'          => 'code',
            'scope'                  => 'openid email profile',
            'state'                  => $state,
            'prompt'                 => 'select_account',
            'include_granted_scopes' => 'true',
        ]);
        return self::AUTH_URL . '?' . $params;
    }

    /**
     * Exchange the authorization code for tokens and fetch the userinfo.
     * Returns ['sub','email','email_verified','name','given_name','family_name','picture'].
     * Throws RuntimeException with a user-safe message on failure.
     */
    public static function fetchUser(string $code): array
    {
        $cfg = self::config();

        // ── Token exchange ──────────────────────────────────────
        $tokenRes = self::httpPost(self::TOKEN_URL, [
            'code'          => $code,
            'client_id'     => $cfg['client_id'],
            'client_secret' => $cfg['client_secret'],
            'redirect_uri'  => $cfg['redirect_uri'],
            'grant_type'    => 'authorization_code',
        ]);

        if (!isset($tokenRes['access_token'])) {
            $desc = $tokenRes['error_description'] ?? $tokenRes['error'] ?? 'unknown error';
            throw new \RuntimeException("Token exchange failed: {$desc}");
        }

        // ── Userinfo ────────────────────────────────────────────
        $info = self::httpGetJson(self::USERINFO_URL, (string) $tokenRes['access_token']);
        if (!isset($info['sub'], $info['email'])) {
            throw new \RuntimeException('Google did not return the required account information.');
        }

        return [
            'sub'            => (string) $info['sub'],
            'email'          => strtolower((string) $info['email']),
            'email_verified' => (bool) ($info['email_verified'] ?? false),
            'name'           => (string) ($info['name'] ?? ''),
            'given_name'     => (string) ($info['given_name'] ?? ''),
            'family_name'    => (string) ($info['family_name'] ?? ''),
            'picture'        => (string) ($info['picture'] ?? ''),
        ];
    }

    /** Generate a single-use CSRF state token, stored in the session. */
    public static function issueState(): string
    {
        $state = bin2hex(random_bytes(16));
        $_SESSION['_google_oauth_state'] = $state;
        return $state;
    }

    /** Verify and consume the stored state token (single-use, timing-safe). */
    public static function verifyState(?string $state): bool
    {
        $expected = $_SESSION['_google_oauth_state'] ?? '';
        unset($_SESSION['_google_oauth_state']); // single-use
        return $state !== null && $expected !== '' && hash_equals($expected, $state);
    }

    // ── Internals ────────────────────────────────────────────

    private static function config(): array
    {
        $cfg = require ROOT_PATH . '/config/app.php';
        return $cfg['google_oauth'] ?? ['client_id' => '', 'client_secret' => '', 'redirect_uri' => ''];
    }

    private static function httpPost(string $url, array $fields): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $body = curl_exec($ch);
        if ($body === false) {
            curl_close($ch);
            throw new \RuntimeException('Could not reach Google. Please try again.');
        }
        curl_close($ch);
        $decoded = json_decode((string) $body, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Unexpected response from Google.');
        }
        return $decoded;
    }

    private static function httpGetJson(string $url, string $token): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $body = curl_exec($ch);
        if ($body === false) {
            curl_close($ch);
            throw new \RuntimeException('Could not reach Google. Please try again.');
        }
        curl_close($ch);
        $decoded = json_decode((string) $body, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Unexpected response from Google.');
        }
        return $decoded;
    }
}
