<?php
/**
 * Email Service
 * Builds transactional emails and hands them to \App\Services\Mailer for
 * actual delivery (SMTP / PHP mail() / log file — see Mailer for config).
 */

declare(strict_types=1);

namespace App\Services;

class EmailService
{
    /**
     * Send an email. Delegates transport to the Mailer driver configured in
     * .env (MAIL_*) or Admin → Settings → Email. Returns true on success.
     */
    public static function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        return Mailer::send($to, $subject, $htmlBody, $textBody);
    }

    // ── Transactional emails ─────────────────────────────────

    public static function sendVerification(string $to, string $firstName, string $token): bool
    {
        // $token is now a 6-digit OTP
        $isOtp   = preg_match('/^\d{6}$/', $token);
        $subject = 'Your verification code – RentalWebsite';

        if ($isOtp) {
            $body = self::otpEmailBody($firstName, $token);
        } else {
            // Legacy link fallback (admin resend or old tokens)
            $cfg  = require ROOT_PATH . '/config/app.php';
            $link = $cfg['url'] . '/auth/verify/' . $token;
            $body = self::renderTemplate('email_verify', ['firstName' => $firstName, 'link' => $link]);
        }
        return self::send($to, $subject, $body);
    }

    private static function otpEmailBody(string $firstName, string $otp): string
    {
        $digits = str_split($otp);
        $digitBoxes = '';
        foreach ($digits as $d) {
            $digitBoxes .= '<span style="display:inline-block;width:44px;height:52px;line-height:52px;'
                . 'text-align:center;font-size:26px;font-weight:800;letter-spacing:0;'
                . 'background:#f0f9f2;border:2px solid #c8e2cf;border-radius:10px;'
                . 'color:#051F20;margin:0 3px;font-family:monospace">' . $d . '</span>';
        }
        $name = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f8fbf9;font-family:Inter,system-ui,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fbf9;padding:40px 0">
  <tr><td align="center">
    <table width="520" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;border:1.5px solid #c8e2cf;overflow:hidden;max-width:520px;width:100%">
      <tr><td style="background:#051F20;padding:28px 40px">
        <p style="margin:0;font-size:20px;font-weight:800;color:#DAF1DE;letter-spacing:-.03em">RentalWebsite</p>
      </td></tr>
      <tr><td style="padding:40px 40px 20px">
        <p style="font-size:22px;font-weight:800;color:#051F20;margin:0 0 10px;letter-spacing:-.03em">Verify your email</p>
        <p style="font-size:15px;color:#5a7a6e;margin:0 0 32px;line-height:1.65">Hi ' . $name . ', enter the 6-digit code below to activate your account. It expires in <strong>10 minutes</strong>.</p>
        <div style="text-align:center;margin-bottom:32px;padding:24px;background:#f8fbf9;border-radius:12px;border:1.5px solid #e2f0e8">'
            . $digitBoxes . '</div>
        <p style="font-size:13px;color:#8ba092;margin:0 0 8px">Or copy your code: <strong style="font-family:monospace;color:#235347;letter-spacing:.1em">' . $otp . '</strong></p>
        <p style="font-size:13px;color:#8ba092;margin:0">If you didn\'t create an account, you can safely ignore this email.</p>
      </td></tr>
      <tr><td style="padding:20px 40px;border-top:1px solid #e2f0e8">
        <p style="font-size:12px;color:#8ba092;margin:0">&copy; ' . date('Y') . ' RentalWebsite. All rights reserved.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>';
    }

    public static function sendPasswordReset(string $to, string $firstName, string $token): bool
    {
        $cfg  = require ROOT_PATH . '/config/app.php';
        $link = $cfg['url'] . '/auth/reset-password/' . $token;

        $subject = 'Reset your password – RentalWebsite';
        $body    = self::renderTemplate('password_reset', [
            'firstName' => $firstName,
            'link'      => $link,
        ]);
        return self::send($to, $subject, $body);
    }

    public static function sendWelcome(string $to, string $firstName, string $role): bool
    {
        $subject = 'Welcome to RentalWebsite';
        $body    = self::renderTemplate('welcome', [
            'firstName' => $firstName,
            'role'      => $role,
        ]);
        return self::send($to, $subject, $body);
    }

    public static function sendPropertyApproved(string $to, string $firstName, string $propertyTitle, string $propertyUrl): bool
    {
        $body = self::renderTemplate('property_approved', [
            'firstName'     => $firstName,
            'propertyTitle' => $propertyTitle,
            'propertyUrl'   => $propertyUrl,
        ]);
        return self::send($to, 'Your property has been approved – RentalWebsite', $body);
    }

    public static function sendPropertyRejected(string $to, string $firstName, string $propertyTitle, string $reason): bool
    {
        $body = self::renderTemplate('property_rejected', [
            'firstName'     => $firstName,
            'propertyTitle' => $propertyTitle,
            'reason'        => $reason,
        ]);
        return self::send($to, 'Your property listing – RentalWebsite', $body);
    }

    public static function sendViewingConfirmed(string $to, string $firstName, array $viewing): bool
    {
        $body = self::renderTemplate('viewing_confirmed', [
            'firstName' => $firstName,
            'viewing'   => $viewing,
        ]);
        return self::send($to, 'Viewing confirmed – RentalWebsite', $body);
    }

    public static function sendViewingRequest(string $to, string $firstName, array $viewing): bool
    {
        $body = self::renderTemplate('viewing_request', [
            'firstName' => $firstName,
            'viewing'   => $viewing,
        ]);
        return self::send($to, 'New viewing request – RentalWebsite', $body);
    }

    // ── Template renderer ─────────────────────────────────────

    private static function renderTemplate(string $template, array $data = []): string
    {
        $file = ROOT_PATH . '/app/Views/emails/' . $template . '.php';
        if (!file_exists($file)) {
            // Fallback plain text
            return self::plainFallback($template, $data);
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean();
    }

    private static function plainFallback(string $template, array $data): string
    {
        $cfg = require ROOT_PATH . '/config/app.php';
        return sprintf(
            '<html><body style="font-family:sans-serif;color:#333;max-width:600px;margin:auto;padding:20px">
            <p>Hi %s,</p><p>%s</p>
            <hr><p style="font-size:12px;color:#999">© %d RentalWebsite. All rights reserved.</p>
            </body></html>',
            htmlspecialchars($data['firstName'] ?? 'there', ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($template, ENT_QUOTES, 'UTF-8'),
            date('Y')
        );
    }
}
