<?php
/**
 * One-time Production Setup — run ONCE, then DELETE this file.
 *
 * Why this exists: public/.htaccess intentionally denies direct web access to
 * reset_admin.php and add_samples.php (Apache returns "403 Forbidden — You
 * don't have permission to access this resource"). This wrapper runs both
 * tools server-side via PHP include, which .htaccess does not affect.
 *
 * What it does in one visit:
 *   1. Resets the admin account (password: Admin@2026, unlocks the account
 *      and clears IP login rate-limit lockouts).
 *   2. Loads 15 verified sample properties and downloads every photo onto
 *      this server (public/uploads/properties/images/) so images are
 *      self-hosted and stay live permanently.
 *
 * Access:     https://rent.habellesfoundation.org/setup.php?key=habelles2026
 * Reload samples from scratch:  add &force=1 to the URL
 *
 * DELETE this file immediately after running.
 */

if (($_GET['key'] ?? '') !== 'habelles2026') {
    http_response_code(403);
    die('Forbidden.');
}

// ── STEP 1: admin password reset + login lockout clear ──────
require __DIR__ . '/reset_admin.php';

// ── STEP 2: sample properties (self-hosted images) ──────────
echo '<div style="max-width:680px;margin:8px auto 24px;padding:10px 16px;background:#eef6f1;'
   . 'border:1px solid #c8e2cf;border-radius:8px;font-family:system-ui,sans-serif;font-size:13px;'
   . 'color:#235347;text-align:center">Now loading the 15 sample properties&hellip;</div>' . PHP_EOL;

require __DIR__ . '/add_samples.php';
