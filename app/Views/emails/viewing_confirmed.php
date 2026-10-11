<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="font-family:Inter,sans-serif;background:#f8f9fa;margin:0;padding:0">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;padding:40px 0">
    <tr><td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)">
            <tr><td style="background:#051F20;padding:32px;text-align:center">
                <h1 style="color:#DAF1DE;margin:0;font-size:24px;font-weight:700">RentalWebsite</h1>
                <p style="color:#8EB69B;margin:8px 0 0;font-size:14px">See it. Understand it. Then decide.</p>
            </td></tr>
            <tr><td style="padding:40px 48px">
                <h2 style="color:#051F20;font-size:22px;margin-top:0">Viewing Confirmed</h2>
                <p style="color:#374151;line-height:1.6">Hi <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>,</p>
                <p style="color:#374151;line-height:1.6">
                    Your viewing for <strong><?= htmlspecialchars($viewing['property_title'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                    has been confirmed.
                </p>
                <div style="background:#f0f9f2;border:1.5px solid #c8e2cf;border-radius:8px;padding:20px;margin:24px 0">
                    <table width="100%">
                        <tr>
                            <td style="padding:6px 0;color:#5a7a6e;font-size:14px;width:40%">Date</td>
                            <td style="padding:6px 0;font-weight:600;font-size:14px;color:#051F20"><?= htmlspecialchars(date('D, d M Y', strtotime($viewing['confirmed_date'] ?? $viewing['preferred_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <td style="padding:6px 0;color:#5a7a6e;font-size:14px">Time</td>
                            <td style="padding:6px 0;font-weight:600;font-size:14px;color:#051F20"><?= htmlspecialchars(date('H:i', strtotime($viewing['confirmed_time'] ?? $viewing['preferred_time'])), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <td style="padding:6px 0;color:#5a7a6e;font-size:14px">Reference</td>
                            <td style="padding:6px 0;font-weight:600;font-size:14px;color:#051F20"><?= htmlspecialchars($viewing['reference'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    </table>
                </div>
                <p style="color:#374151;line-height:1.6;font-size:14px">
                    Our agent will meet you at the property. Please bring this confirmation email and a valid ID.
                    You can cancel up to 24 hours before the viewing.
                </p>
            </td></tr>
            <tr><td style="background:#f9fafb;padding:24px;text-align:center;border-top:1px solid #e5e7eb">
                <p style="color:#9ca3af;font-size:12px;margin:0">&copy; <?= date('Y') ?> RentalWebsite. All rights reserved.</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body></html>
