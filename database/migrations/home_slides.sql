-- ─────────────────────────────────────────────────────────────
-- Home page hero slides (admin-managed via CMS)
-- Apply to production: import this file in cPanel/phpMyAdmin
-- ─────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS home_slides (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('image','video') NOT NULL DEFAULT 'image',
  media_path VARCHAR(500) DEFAULT NULL,        -- local 'uploads/hero/...' or absolute http(s) URL
  label VARCHAR(150) DEFAULT '',
  title VARCHAR(255) DEFAULT '',
  subtitle VARCHAR(500) DEFAULT '',
  btn1_text VARCHAR(80) DEFAULT '',
  btn1_url VARCHAR(300) DEFAULT '',
  btn2_text VARCHAR(80) DEFAULT '',
  btn2_url VARCHAR(300) DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed with the slides currently hardcoded on the home page
-- (only inserted when the table is empty)
INSERT INTO home_slides (type, media_path, label, title, subtitle, btn1_text, btn1_url, btn2_text, btn2_url, sort_order, active)
SELECT * FROM (
  SELECT
    'image' AS type,
    'https://images.unsplash.com/photo-1560185007-cde436f6a4d0?w=1800&q=80&auto=format&fit=crop' AS media_path,
    'Property Information Platform' AS label,
    'See it.\nUnderstand it.\nThen decide.' AS title,
    'Know the dimensions, road access, utilities and neighbourhood before you spend money travelling to inspect.' AS subtitle,
    'Browse properties' AS btn1_text, 'properties' AS btn1_url,
    'How it works' AS btn2_text, 'how-it-works' AS btn2_url,
    0 AS sort_order, 1 AS active
  UNION ALL SELECT 'image','https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1800&q=80&auto=format&fit=crop',
    'Agency Verified Listings','Every property\nphysically inspected\nby our team.',
    'Our agents confirm measurements, photos, utilities and road accessibility before any listing goes live.',
    'View verified listings','properties','','',10,1
  UNION ALL SELECT 'image','https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1800&q=80&auto=format&fit=crop',
    'For Landlords','Fewer wasted\nvisits.\nBetter tenants.',
    'Renters who request a viewing have already reviewed your property details. Every visit is a serious one.',
    'List your property','auth/register','Learn more','for-landlords',20,1
  UNION ALL SELECT 'image','https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=1800&q=80&auto=format&fit=crop',
    'Room Dimensions Included','Exact room sizes.\nRoad access times.\nNo surprises.',
    'Every listing includes measured room dimensions, walking time to the main road, vehicle access and utility details.',
    'Start searching','properties','','',30,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM home_slides);
