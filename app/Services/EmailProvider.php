<?php
/**
 * Email Provider — thin abstraction over the existing Mailer transport.
 *
 * The app already ships a production-grade SMTP/mail/log Mailer configured
 * via MAIL_* env vars (see app/Services/Mailer.php). This provider wraps it
 * behind the same call shape as SmsProvider so NotificationService can treat
 * both channels uniformly and alternative transports can be swapped in later.
 *
 * EMAIL_PROVIDER env (optional metadata / future drivers):
 *   smtp | mail | log | (empty = follow Mailer defaults)
 * Credentials stay in MAIL_* variables — never hard-coded.
 */

declare(strict_types=1);

namespace App\Services;

class EmailProvider
{
    /**
     * Send an email. Returns [ok(bool), providerMessageId(?string), error(?string)].
     *
     * @param string $to      validated recipient email
     * @param string $subject subject line
     * @param string $html    rendered HTML body
     * @param string $text    plain-text fallback
     */
    public static function send(string $to, string $subject, string $html, string $text = ''): array
    {
        $to = trim($to);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return [false, null, 'Invalid email address'];
        }

        try {
            $ok = Mailer::send($to, $subject, $html, $text);
        } catch (\Throwable $e) {
            return [false, null, 'Email transport error: ' . mb_substr($e->getMessage(), 0, 200)];
        }

        if (!$ok) {
            return [false, null, 'Email delivery failed (see mail_error.log)'];
        }
        // Mailer does not return a provider message id; use a local trace id.
        return [true, 'mail-' . bin2hex(random_bytes(6)), null];
    }

    /** Current configured driver name (for the outbound log). */
    public static function driverName(): string
    {
        $driver = trim((string) ($_ENV['MAIL_DRIVER'] ?? ''));
        if ($driver === 'smtp' || $driver === 'mail' || $driver === 'log') {
            return $driver;
        }
        return filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN) ? 'log' : 'mail';
    }
}
