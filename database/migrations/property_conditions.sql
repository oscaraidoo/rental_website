-- ─────────────────────────────────────────────────────────────
-- Property condition assessments (admin-managed module)
-- One property can have multiple condition reports over time;
-- the newest active one is shown on the public property page.
-- Apply to production: import this file in cPanel/phpMyAdmin.
-- ─────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS property_conditions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  property_id INT UNSIGNED NOT NULL,
  overall_condition ENUM('excellent','good','fair','needs_repair','poor') NOT NULL DEFAULT 'good',
  structure ENUM('excellent','good','fair','needs_repair','poor') DEFAULT NULL,
  plumbing ENUM('excellent','good','fair','needs_repair','poor') DEFAULT NULL,
  electrical ENUM('excellent','good','fair','needs_repair','poor') DEFAULT NULL,
  paint_finishing ENUM('excellent','good','fair','needs_repair','poor') DEFAULT NULL,
  fittings ENUM('excellent','good','fair','needs_repair','poor') DEFAULT NULL,
  roof ENUM('excellent','good','fair','needs_repair','poor') DEFAULT NULL,
  windows_doors ENUM('excellent','good','fair','needs_repair','poor') DEFAULT NULL,
  cleanliness ENUM('excellent','good','fair','needs_repair','poor') DEFAULT NULL,
  summary TEXT,                                   -- short public-facing report text
  issues TEXT,                                    -- known issues / defects (public)
  internal_notes TEXT,                            -- admin-only notes
  photos TEXT DEFAULT NULL,                       -- JSON array of ['path'=>..,'caption'=>..]
  assessed_by INT UNSIGNED NOT NULL,              -- admin user id
  assessment_date DATE DEFAULT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,           -- newest active report shows publicly
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_pc_property (property_id, active),
  CONSTRAINT fk_propcond_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
  CONSTRAINT fk_propcond_assessor FOREIGN KEY (assessed_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
