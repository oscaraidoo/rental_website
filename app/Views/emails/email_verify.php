<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family:Inter,sans-serif;background:#f8f9fa;margin:0;padding:0">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;padding:40px 0">
    <tr><td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)">
            <!-- Header -->
            <tr><td style="background:#2563eb;padding:32px;text-align:center">
                <h1 style="color:#fff;margin:0;font-size:24px;font-weight:700">
                    RentalWebsite
                </h1>
                <p style="color:#bfdbfe;margin:8px 0 0;font-size:14px">See it. Understand it. Then decide.</p>
            </td></tr>
            <!-- Body -->
            <tr><td style="padding:40px 48px">
                <h2 style="color:#1e3a5f;font-size:22px;margin-top:0">Verify your email address</h2>
                <p style="color:#374151;line-height:1.6">Hi <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>,</p>
                <p style="color:#374151;line-height:1.6">
                    Thanks for creating an account on RentalWebsite. To get started, please verify your email address by clicking the button below.
                </p>
                <div style="text-align:center;margin:32px 0">
                    <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>"
                       style="background:#2563eb;color:#fff;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:600;font-size:16px;display:inline-block">
                        Verify Email Address
                    </a>
                </div>
                <p style="color:#6b7280;font-size:14px">
                    Or copy and paste this link into your browser:<br>
                    <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" style="color:#2563eb;word-break:break-all">
                        <?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </p>
                <p style="color:#9ca3af;font-size:13px">This link expires in 24 hours. If you did not create an account, you can safely ignore this email.</p>
            </td></tr>
            <!-- Footer -->
            <tr><td style="background:#f9fafb;padding:24px;text-align:center;border-top:1px solid #e5e7eb">
                <p style="color:#9ca3af;font-size:12px;margin:0">© <?= date('Y') ?> RentalWebsite. All rights reserved.</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body></html>
