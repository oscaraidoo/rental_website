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
                <h2 style="color:#92400e;font-size:22px;margin-top:0">New Viewing Request</h2>
                <p style="color:#374151;line-height:1.6">Hi <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>,</p>
                <p style="color:#374151;line-height:1.6">
                    You have received a new viewing request for
                    <strong><?= htmlspecialchars($viewing['property_title'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>.
                </p>
                <div style="background:#fffbeb;border-radius:8px;padding:20px;margin:24px 0">
                    <table width="100%">
                        <tr>
                            <td style="padding:5px 0;color:#6b7280;font-size:14px;width:45%">Renter</td>
                            <td style="padding:5px 0;font-size:14px"><?= htmlspecialchars($viewing['renter_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#6b7280;font-size:14px">Preferred Date</td>
                            <td style="padding:5px 0;font-size:14px"><?= htmlspecialchars(date('D, d M Y', strtotime($viewing['preferred_date'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#6b7280;font-size:14px">Attendees</td>
                            <td style="padding:5px 0;font-size:14px"><?= htmlspecialchars($viewing['num_attendees'] ?? '1', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    </table>
                </div>
                <p style="color:#374151;font-size:14px">
                    Log in to review and confirm this request.
                </p>
            </td></tr>
            <tr><td style="background:#f9fafb;padding:24px;text-align:center;border-top:1px solid #e5e7eb">
                <p style="color:#9ca3af;font-size:12px;margin:0">© <?= date('Y') ?> RentalWebsite. All rights reserved.</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body></html>
