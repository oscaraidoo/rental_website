# RentalWebsite — Property Information & Viewing Management Platform

> **"See it. Understand it. Then decide."**

Ghana's professional accommodation marketplace built on PHP 8 + MySQL 8 + Bootstrap 5.

---

## Features

- ✅ Full property listings with verified dimensions, road accessibility, utilities, and photos
- ✅ Two user roles: Super Admin, Renter
- ✅ Property verification workflow (submit → review → inspect → verify → publish)
- ✅ Physical viewing request system with configurable viewing fee
- ✅ Payment architecture (Paystack/Flutterwave-ready, manual/dev mode included)
- ✅ Property comparison (up to 4 properties side-by-side)
- ✅ Advanced search with 20+ filters
- ✅ Agency commission management (fully configurable percentages)
- ✅ Digital agreement signing with audit trail
- ✅ In-app + email notification system
- ✅ Review system (after completed viewings only)
- ✅ Property reporting system
- ✅ Full CMS (pages, FAQs, blog)
- ✅ Admin dashboard with charts and reports
- ✅ CSV export for all reports
- ✅ Audit log for all important actions
- ✅ Rate limiting, CSRF protection, XSS prevention, bcrypt passwords
- ✅ Mobile-first responsive design
- ✅ REST API endpoints for future mobile app
- ✅ One-click installer

---

## Quick Start

### Requirements

| Requirement | Minimum |
|---|---|
| PHP | 8.0+ |
| MySQL | 8.0+ |
| Extensions | PDO, PDO_MySQL, GD, mbstring, fileinfo, json |
| Web Server | Apache with mod_rewrite |

### Installation (XAMPP)

1. **Place files** in `c:\xampp\htdocs\rental_website\`

2. **Start** Apache and MySQL in XAMPP Control Panel

3. **Open the installer:**
   ```
   http://localhost/rental_website/public/install.php
   ```

4. **Fill in** your database credentials and admin account

5. **Click Install** — the installer will:
   - Create the database
   - Import the full schema (47 tables)
   - Insert seed data (roles, regions, property types, amenities, settings)
   - Create your admin account
   - Update `.env` automatically

6. **Delete the installer:**
   ```
   Delete: public/install.php
   ```

7. **Access the platform:**
   - Public site: `http://localhost/rental_website/public/`
   - Admin panel: `http://localhost/rental_website/public/admin`

### Manual Installation (Alternative)

```bash
# 1. Create the database
mysql -u root -p -e "CREATE DATABASE rental_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Import schema (single consolidated structural file - all 47 tables)
mysql -u root -p rental_website < database/schema.sql

# 3. Import seed data
mysql -u root -p rental_website < database/seeds.sql

# 4. (Optional) Demo data: 3 landlords, 1 renter, 12 verified properties
mysql -u root -p rental_website < database/sample_properties.sql
```

Then edit `.env` with your credentials.

---

## Default Admin Credentials

After installation with default seed data:

| Field | Value |
|---|---|
| Email | `admin@rentalwebsite.com` |
| Password | Change this — it's the bcrypt hash of `password` in the seed |

> ⚠️ **Change the admin password immediately after installation.**

---

## Directory Structure

```
rental_website/
├── app/
│   ├── Controllers/
│   │   ├── Admin/          # 14 admin controllers
│   │   ├── Auth/           # Login, Register, Password, Google
│   │   ├── Renter/         # 8 renter controllers
│   │   ├── Api/            # 4 API controllers
│   │   └── PublicController.php
│   ├── Models/             # 7 models
│   ├── Views/              # All templates
│   │   ├── admin/
│   │   ├── landlord/
│   │   ├── renter/
│   │   ├── public/
│   │   ├── layouts/        # main, admin, landlord, renter
│   │   ├── partials/       # navbar, footer, sidebars
│   │   └── emails/         # Email templates
│   ├── Helpers/            # Database, Security, Str, Upload, etc.
│   ├── Services/           # Email, Notification, Audit
│   └── Middleware/         # AuthMiddleware
├── config/
│   ├── app.php
│   └── database.php
├── database/
│   ├── schema.sql          # Single consolidated schema - all 47 tables
│   ├── seeds.sql           # Roles, regions, types, amenities, settings
│   └── sample_properties.sql  # Optional demo data (12 properties)
├── public/
│   ├── index.php           # Front controller
│   ├── .htaccess           # URL rewriting
│   ├── install.php         # One-click installer
│   └── assets/
│       ├── css/            # app.css, admin.css, dashboard.css
│       └── js/             # app.js, admin.js
├── routes/
│   └── web.php             # All routes (~80 routes)
├── storage/
│   ├── uploads/            # Property images, videos, avatars
│   └── logs/               # mail.log, error logs
├── bootstrap/
│   └── app.php             # Autoloader, session, helpers
└── .env                    # Environment configuration
```

---

## Configuration

All key settings are managed from the **Admin Panel → Settings**:

| Setting | Location |
|---|---|
| Commission rate (%) | Admin → Settings → Fees |
| Viewing fee (GH₵) | Admin → Settings → Fees |
| Registration fee | Admin → Settings → Fees |
| Max images per property | Admin → Settings → Properties |
| Login attempt limit | Admin → Settings → Security |
| Email configuration | Admin → Settings → Email |
| Properties per page | Admin → Settings → Search |

---

## Google Sign-In (OAuth 2.0)

"Continue with Google" is built in and activates automatically once credentials are configured — until then the buttons stay hidden and the existing email/password login is untouched.

### 1. Create Google credentials

1. Open the [Google Cloud Console](https://console.cloud.google.com/apis/credentials) → **Create credentials → OAuth client ID → Web application**
2. Add an **Authorised redirect URI**:
   ```
   {APP_URL}/auth/google/callback
   e.g. http://localhost/rental_website/public/auth/google/callback
   ```
3. Copy the **Client ID** and **Client secret**

### 2. Configure `.env`

```dotenv
GOOGLE_CLIENT_ID=xxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxx
```

### 3. Existing installs — one-time DB migration

Fresh installs already include the required columns via `database/schema.sql` (the single consolidated schema). If your database was created before Google auth (v1.0), add them once:

```sql
ALTER TABLE `users`
    ADD COLUMN `auth_provider` VARCHAR(20) NOT NULL DEFAULT 'local'
        COMMENT 'local, google' AFTER `password`,
    ADD COLUMN `google_id` VARCHAR(255) DEFAULT NULL
        COMMENT 'Google account subject id (sub claim)' AFTER `auth_provider`,
    ADD UNIQUE KEY `uq_users_google_id` (`google_id`),
    ADD KEY `idx_users_auth_provider` (`auth_provider`);
```

### How it works

| Scenario | Behaviour |
|---|---|
| Google account already linked | Straight login |
| Existing local account with the same (verified) Google email | Google account linked to it, then login |
| No account yet | Auto-registered (email pre-verified); the role comes from the register form — Renter by default, or Landlord if selected before clicking the Google button. A password-reset email is also sent so the user can optionally set a password |

- Suspended accounts are refused; unverified Google emails are rejected (prevents account takeover via an unverified mailbox).
- Audit log events: `login_google`, `google_account_linked`, `register_google`.
- Relevant files: `app/Controllers/Auth/GoogleAuthController.php`, `app/Helpers/GoogleOAuth.php`; routes `/auth/google` and `/auth/google/callback`.

---

## API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/properties` | List verified properties |
| GET | `/api/properties/:id` | Single property detail |
| GET | `/api/search` | Search with filters |
| POST | `/api/properties/:id/favorite` | Toggle save (auth required) |
| GET | `/api/notifications` | User notifications |
| POST | `/api/notifications/:id/read` | Mark notification read |
| GET | `/api/notification-settings` | View email/SMS notification preferences (auth) |
| POST | `/api/notification-settings` | Update preferences: email/sms toggles, phone, muted types (auth) |
| POST | `/api/notification-settings/test` | Send a test notification to yourself: `channel=email\|sms`, `type=<TYPE_ID>` (auth) |
| GET | `/admin/notifications/log` | Admin: outbound notification history with filters (auth: admin) |
| POST | `/admin/notifications/log/:id/retry` | Admin: retry a failed notification (auth: admin) |
| GET | `/api/viewings` | User's viewings |
| GET | `/api/regions` | All regions |
| GET | `/api/cities/:regionId` | Cities in a region |
| GET | `/api/areas/:cityId` | Areas in a city |

---

## Workflow

```
Property added by the agency → Admin reviews → Physical inspection →
Admin verifies → Property goes live → Renters search →
Renter requests viewing → Pays viewing fee → Viewing confirmed →
Physical visit → Renter leaves review → Property rented
```

---

## Notifications (Email + SMS)

All outbound email/SMS runs through one service: `\App\Services\NotificationService::send()`.
Features trigger notifications by **type id**, never by calling Mailer/SMS gateways directly:

```php
\App\Services\NotificationService::send(
    userId: $userId,
    type: 'PAYMENT_SUCCESSFUL',
    data: ['purpose' => 'Viewing fee', 'amount' => 'GHS 50', 'reference' => $txnRef],
    channels: null,        // null = template default ['email','sms']; or restrict: ['email']
    idemKey: 'payment_' . $paymentId  // optional dedupe: same user+key+channel within 24h is skipped
);
```

- **Templates** live in `app/Services/NotificationTemplates.php` (subject / message / sms / channels per type). Add a new type there and it appears automatically in the user settings page and admin log filters.
- **Preferences** (`notification_channel_prefs` table): email/sms global toggles, an E.164 phone number, and per-type mute list. `send()` respects them before dispatching (users' page: `/renter/notifications/settings`).
- **Delivery log** (`notification_outbound` table): every attempted send is recorded with status `sent|failed|pending`, attempts, provider message id and error. Admin page: `/admin/notifications/log` (filter by channel/type/status, retry failed).
- **Providers** are swappable via env — no business-logic changes:

```dotenv
# Email — uses the existing MAIL_* driver (log for dev, SMTP in production)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=Your Agency

# SMS — provider abstraction; 'log' driver for development
SMS_PROVIDER=log
# SMS_PROVIDER=twilio|hubtel|arkesel  (then add its credentials)
# SMS_API_KEY=...                     # never hard-coded; read by the provider only
# SMS_API_SECRET=...
# SMS_FROM=+233XXXXXXXXXX
```

Existing installs need the one-time migration in `database/migrations/multichannel_notifications.sql` (additive only — two new tables, nothing modified).

Tests (`php storage/tests/multichannel_notification_test.php`) run entirely on the `log` drivers — no real email/SMS is ever sent.

---

## Payment Integration

The system is architected to support:
- **Paystack** — add keys to `.env` and implement `PaystackService`
- **Flutterwave** — add keys to `.env` and implement `FlutterwaveService`

In `APP_ENV=development`, payments are **auto-confirmed** so you can test the full flow without a gateway.

---

## Security

- `password_hash()` / `password_verify()` with bcrypt cost 12
- PDO prepared statements throughout — no raw SQL string concatenation
- CSRF tokens on every POST form
- Output escaped with `htmlspecialchars()` / `Security::e()`
- Rate limiting on login, password reset, and contact form
- File upload validation: MIME type (finfo), extension, size
- PHP execution blocked in `storage/uploads/` via `.htaccess`
- Session regeneration on login
- Secure, HttpOnly, SameSite cookies

---

## License

Built for production use. Customise freely for your agency.
