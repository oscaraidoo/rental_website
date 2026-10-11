-- Landlord contact details unlock system
-- Run in phpMyAdmin on both local (rental_website) and production (habelles_rent)

-- Store per-property landlord contact details (posted by admin)
ALTER TABLE `landlords`
  ADD COLUMN IF NOT EXISTS `contact_phone`   VARCHAR(30)  DEFAULT NULL COMMENT 'Contact phone shown after unlock',
  ADD COLUMN IF NOT EXISTS `contact_phone2`  VARCHAR(30)  DEFAULT NULL COMMENT 'Alternate phone',
  ADD COLUMN IF NOT EXISTS `contact_email`   VARCHAR(191) DEFAULT NULL COMMENT 'Direct contact email',
  ADD COLUMN IF NOT EXISTS `contact_address` VARCHAR(500) DEFAULT NULL COMMENT 'Office/home address',
  ADD COLUMN IF NOT EXISTS `contact_notes`   TEXT         DEFAULT NULL COMMENT 'Any extra instructions for renters';

-- Allow payments to be tagged as landlord-contact unlocks
ALTER TABLE `payments`
  MODIFY COLUMN `purpose` ENUM('viewing_fee','registration_fee','verification_fee','agency_commission','featured_listing','renewal_fee','landlord_contact_unlock','other')
  NOT NULL;

-- Track which renters have paid to unlock a specific property's landlord contact
CREATE TABLE IF NOT EXISTS `landlord_contact_unlocks` (
  `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `property_id`  INT(10) UNSIGNED NOT NULL,
  `user_id`      INT(10) UNSIGNED NOT NULL,
  `amount`       DECIMAL(10,2)    NOT NULL DEFAULT 50.00,
  `payment_ref`  VARCHAR(60)      NOT NULL,
  `status`       ENUM('pending','paid','failed') NOT NULL DEFAULT 'paid',
  `paid_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_unlock` (`property_id`, `user_id`),
  KEY `idx_user`     (`user_id`),
  KEY `idx_property` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed the unlock fee (50 GHC) — idempotent so re-runs won't duplicate.
INSERT INTO `system_settings` (`key`, `value`, `type`, `group`, `label`, `description`, `is_public`)
VALUES ('landlord_contact_fee', '50.00', 'float', 'fees', 'Landlord Contact Unlock Fee', 'One-time fee renters pay to view landlord contact details for a property', 0)
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
