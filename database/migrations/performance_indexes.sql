-- ─────────────────────────────────────────────────────────────
-- Performance indexes — hot-path query coverage.
-- Apply to production: import this file in cPanel/phpMyAdmin.
-- ─────────────────────────────────────────────────────────────

-- Cover the correlated subselects in PropertyModel::search() —
-- every search/listing row resolves its primary image with
-- WHERE property_id = ? AND is_primary = 1.
ALTER TABLE property_images
  ADD INDEX idx_pi_prop_primary (property_id, is_primary);

-- Blog listing: WHERE status='published' ORDER BY published_at DESC
ALTER TABLE blog_posts
  ADD INDEX idx_blog_status_pub (status, published_at);

-- FAQ listing: WHERE active=1 ORDER BY category, sort_order
ALTER TABLE faqs
  ADD INDEX idx_faq_active_sort (active, category, sort_order);
