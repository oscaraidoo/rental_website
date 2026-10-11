<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="font-family:Inter,sans-serif;background:#f8f9fa;margin:0;padding:0">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;padding:40px 0">
    <tr><td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)">
            <tr><td style="background:#051F20;padding:32px;text-align:center">
                <h1 style="color:#DAF1DE;margin:0;font-size:24px;font-weight:700">RentalWebsite</h1>
            </td></tr>
            <tr><td style="padding:40px 48px">
                <h2 style="color:#166534;font-size:22px;margin-top:0">Your property is live!</h2>
                <p style="color:#374151;line-height:1.6">Hi <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>,</p>
                <p style="color:#374151;line-height:1.6">
                    Great news! Your property <strong><?= htmlspecialchars($propertyTitle, ENT_QUOTES, 'UTF-8') ?></strong>
                    has been reviewed, verified, and is now live on RentalWebsite.
                </p>
                <div style="text-align:center;margin:32px 0">
                    <a href="<?= htmlspecialchars($propertyUrl, ENT_QUOTES, 'UTF-8') ?>"
                       style="background:#16a34a;color:#fff;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:600;font-size:16px;display:inline-block">
                        View Your Listing
                    </a>
                </div>
                <p style="color:#6b7280;font-size:14px">Renters can now view your property details and request viewings. You will be notified when a viewing request is received.</p>
            </td></tr>
            <tr><td style="background:#f9fafb;padding:24px;text-align:center;border-top:1px solid #e5e7eb">
                <p style="color:#9ca3af;font-size:12px;margin:0">© <?= date('Y') ?> RentalWebsite. All rights reserved.</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body></html>
