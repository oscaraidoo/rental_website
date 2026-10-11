-- ─────────────────────────────────────────────────────────────
-- Lightweight performance monitoring (no personal data).
-- One row per HTTP request: duration, query stats, PHP errors.
-- Apply to production: import this file in cPanel/phpMyAdmin.
-- ─────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS perf_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  day DATE NOT NULL,                          -- grouping column (Africa/Accra date)
  route VARCHAR(190) NOT NULL,                -- request path, no query string
  method VARCHAR(10) NOT NULL DEFAULT 'GET',
  status_code SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  duration_ms INT UNSIGNED NOT NULL DEFAULT 0,
  query_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  query_time_ms INT UNSIGNED NOT NULL DEFAULT 0,
  slow_queries SMALLINT UNSIGNED NOT NULL DEFAULT 0,   -- queries > 100ms
  memory_kb INT UNSIGNED NOT NULL DEFAULT 0,
  error_type VARCHAR(32) DEFAULT NULL,        -- 'exception', 'fatal', 'error'
  error_message VARCHAR(500) DEFAULT NULL,    -- no personal data, truncated
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_perf_day (day),
  INDEX idx_perf_route (route),
  INDEX idx_perf_duration (duration_ms),
  INDEX idx_perf_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
