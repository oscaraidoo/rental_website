<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="font-family:Inter,sans-serif;background:#f8f9fa;margin:0;padding:0">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;padding:40px 0">
    <tr><td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)">
            <tr><td style="background:#051F20;padding:32px;text-align:center">
                <h1 style="color:#DAF1DE;margin:0;font-size:24px;font-weight:700">RentalWebsite</h1>
                <p style="color:#8EB69B;margin:8px 0 0">See it. Understand it. Then decide.</p>
            </td></tr>
            <tr><td style="padding:40px 48px">
                <h2 style="color:#1e3a5f">Welcome, <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>!</h2>
                <p style="color:#374151;line-height:1.6">Your account is now verified and ready to use.</p>
                <?php if (($role ?? '') === 'landlord'): ?>
                <p style="color:#374151;line-height:1.6">As a landlord, you can now list your properties and connect with qualified renters. Our team will verify each listing before it goes live — ensuring your property is presented professionally.</p>
                <?php else: ?>
                <p style="color:#374151;line-height:1.6">As a renter, you can now search verified properties with detailed information including dimensions, road accessibility, utilities, and photos — so you can make an informed decision before visiting.</p>
                <?php endif; ?>
            </td></tr>
            <tr><td style="background:#f9fafb;padding:24px;text-align:center;border-top:1px solid #e5e7eb">
                <p style="color:#9ca3af;font-size:12px;margin:0">© <?= date('Y') ?> RentalWebsite. All rights reserved.</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body></html>
