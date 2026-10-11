-- ─────────────────────────────────────────────────────────────
-- Multi-channel notifications (Email + SMS) — outbound log and
-- per-user channel preferences. Complements the existing
-- `notifications` (in-app) table; nothing existing is modified.
-- Apply to production: import in cPanel/phpMyAdmin (additive only).
-- ─────────────────────────────────────────────────────────────

-- 1. Outbound notification log (one row per channel attempt batch)
CREATE TABLE IF NOT EXISTS notification_outbound (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,            -- NULL for system/guest targets
  notification_type VARCHAR(80) NOT NULL,       -- e.g. PAYMENT_SUCCESSFUL
  channel ENUM('email','sms') NOT NULL,
  recipient VARCHAR(191) NOT NULL,              -- email address or E.164 phone
  subject VARCHAR(255) DEFAULT NULL,            -- email subject (NULL for sms)
  body_preview VARCHAR(500) DEFAULT NULL,       -- rendered text preview (no secrets)
  template VARCHAR(80) DEFAULT NULL,            -- template id used
  status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  provider VARCHAR(40) DEFAULT NULL,            -- smtp / log / twilio / arkesel ...
  provider_message_id VARCHAR(191) DEFAULT NULL,
  error_message VARCHAR(500) DEFAULT NULL,      -- sanitised provider error
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  idempotency_key VARCHAR(150) DEFAULT NULL,    -- dedupe: type+entity, per user
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sent_at TIMESTAMP NULL DEFAULT NULL,
  INDEX idx_out_user (user_id),
  INDEX idx_out_type (notification_type),
  INDEX idx_out_status (status),
  INDEX idx_out_created (created_at),
  INDEX idx_out_idem (idempotency_key),
  CONSTRAINT fk_out_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Per-user channel preferences (email/sms toggles + per-type opt-out)
CREATE TABLE IF NOT EXISTS notification_channel_prefs (
  user_id INT UNSIGNED NOT NULL,
  email_enabled TINYINT(1) NOT NULL DEFAULT 1,
  sms_enabled TINYINT(1) NOT NULL DEFAULT 1,
  phone_e164 VARCHAR(20) DEFAULT NULL,          -- normalised +233...
  muted_types TEXT DEFAULT NULL,                -- JSON array of type ids user muted
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_pref_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed prefs rows for existing users (defaults = everything on)
INSERT IGNORE INTO notification_channel_prefs (user_id, email_enabled, sms_enabled)
  SELECT id, 1, 1 FROM users;
