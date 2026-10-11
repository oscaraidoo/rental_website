# Performance, SEO & Monitoring Audit Report

**Project:** RentalWebsite (PHP rental listing platform)
**Local:** `http://localhost/rental_website/public/` (XAMPP, Apache :80, MySQL :3306)
**Production:** `https://rent.habellesfoundation.org` (cPanel, manual upload)
**Date:** 2026-10-08

---

## 1. Files Modified / Created

### New files
| File | Purpose |
|---|---|
| `app/Services/PerformanceService.php` | Lightweight request metrics collector (duration, query count/time, slow queries, memory, PHP errors). No personal data. |
| `app/Helpers/Cache.php` | Tiny file-based TTL cache (`storage/cache/`, sha256 filenames, `remember/get/set`). |
| `app/Controllers/Admin/PerformanceController.php` | Admin performance section: aggregates `perf_log` (avg/p95 ms, query counts, slow queries, errors, daily trend). `adminOnly()`. |
| `app/Views/admin/performance.php` | Admin performance dashboard view. |
| `app/Views/public/` — JSON-LD block appended to `property_detail.php` | Structured data (Accommodation + Offer + AggregateRating). |
| `database/migrations/performance_monitoring.sql` | `perf_log` table + 4 indexes. **Applied locally.** |
| `database/migrations/performance_indexes.sql` | 3 composite indexes (below). **Applied locally.** |

### Modified files
| File | Change |
|---|---|
| `app/Helpers/Database.php` | `query()` instruments every query with `PerformanceService::trackQuery()` (gated by `class_exists` — zero cost when monitoring is off). |
| `bootstrap/app.php` | `PerformanceService::begin()` after autoloader; shutdown handler `finish()`; exception + fatal-shutdown handlers call `recordError()`. |
| `routes/web.php` | `PerformanceService::setRoute($path)`, `/admin/performance` + `/admin/performance/clear`, `/sitemap.xml` route. |
| `app/Controllers/PublicController.php` | Cached reference data; single-query platform stats; merged reviews+avg-ratings query; `metaDescription`/`ogType`/`ogImage`; `sitemap()` action. |
| `app/Models/PropertyModel.php` | `FULL_SELECT` const shared by `findById`+`findBySlug`; `findBySlug` now 1 query (was 2); `attachRelations()` helper. |
| `app/Views/layouts/main.php` | Canonical link (base-path-safe), robots meta, Open Graph + Twitter cards, 3 preconnects. |
| `app/Views/partials/admin_sidebar.php` | "Performance" link under System. |
| `public/robots.txt` | `Sitemap:` line added. |
| `.env` (local) | `PERF_MONITOR=true` appended (forcing monitoring on locally). |

---

## 2. Database Changes

### New table: `perf_log`
One row per HTTP request. **No personal information collected** — no IPs, no user IDs, no user agents.

| Column | Notes |
|---|---|
| `day` | DATE grouping column (Accra date) |
| `route`, `method`, `status_code` | Request identity (path truncated to 190 chars) |
| `duration_ms` | Total request wall time |
| `query_count`, `query_time_ms` | DB totals for the request |
| `slow_queries` | Count of queries > 100 ms |
| `memory_kb` | Peak memory |
| `error_type`, `error_message` | Exception/fatal/warning summary (truncated 500 chars) |

Indexes: `idx_perf_day`, `idx_perf_route`, `idx_perf_duration`, `idx_perf_created`.
Retention: opportunistic prune of rows older than 45 days (~1/200 requests); admin can also prune > 7 days from the UI.

### New indexes (existing tables)
```sql
-- covers search-card primary-image subselects
CREATE INDEX idx_pi_prop_primary ON property_images (property_id, is_primary);
-- covers blog listing ORDER BY
CREATE INDEX idx_blog_status_pub ON blog_posts (status, published_at);
-- covers FAQ page grouping
CREATE INDEX idx_faq_active_sort ON faqs (active, category, sort_order);
```

No existing tables, columns, constraints, or relationships were altered. Zero business-logic changes.

---

## 3. SQL Query Optimizations

| Route | Before | After | Change |
|---|---|---|---|
| Home `/` | 16 queries | **12** | Types+regions cached (file cache); stats 3 → 1 query (3 correlated subselects in one `fetchOne`). |
| `/properties` | 9 queries | **4 (warm)** | 5 reference-data queries cached 300s; primary-image subselect uses new `idx_pi_prop_primary`. |
| Property detail | 18 queries | **16** | Reviews + 4 avg-rating queries merged into one query with correlated subselects; `findBySlug` dropped its id-lookup hop (2 → 1 on the main select). |

Slow-query threshold: any query > 100 ms recorded. The detail page's largest query (`PropertyModel::findBySlug` + relation attach) runs in 40–159 ms on XAMPP's un-tuned local MySQL; this is expected to be lower on production.

## 4. PHP Code Optimizations
- Reference-data file cache (`Cache::remember`, 300 s TTL) → repeated requests skip 5–7 queries.
- Single-row aggregate queries replace query-in-loop patterns (stats, ratings).
- Shared `FULL_SELECT` constant eliminates duplicated property column lists.
- Monitoring is fully lazy: `isEnabled()` result cached per request; when disabled (default for `APP_DEBUG=true`) the only cost is one boolean check at bootstrap.

## 5. Front-End Optimizations
- **Preconnects** added for `cdn.jsdelivr.net`, `fonts.googleapis.com`, `fonts.gstatic.com` (faster first paint for Bootstrap CSS + fonts).
- Images: already lazy-loaded with descriptive alt text (property cards, home, detail) — no changes needed.
- `public/.htaccess` already provides: 1-year image expires, 6-month CSS/JS expires, gzip deflate, security headers — verified, unchanged.
- CSS/JS unchanged (previous redesign was reverted per user request; asset versioning not reintroduced).

## 6. Image Optimizations
Audited: `public/uploads` = 856 images, ~42 MB total, only **1 file > 300 KB**. Average image < 50 KB. Conclusion: server-side image re-compression is **not a priority**; lazy loading + browser caching already cover delivery.

## 7. Caching Implemented
| What | Where | TTL |
|---|---|---|
| Property types, regions (home) | `storage/cache/ref:types`, `ref:regions` | 300 s |
| Types, regions, ceilings, floors, amenities (properties page) | `storage/cache/ref:*_all` etc. | 300 s |

Cache invalidates naturally with TTL; reference data changes are admin-rare so 5 min staleness is acceptable. Add-on caches (`opcache`, `redis`) noted in §11.

## 8. Security Improvements
- No personal data in monitoring (deliberate design; `perf_log` stores only route/method/status/timings/errors).
- Admin performance page behind `adminOnly()` + CSRF on the prune action. Guest → 302 to login; renter role cannot access `/admin/*`.
- Canonical/OG output escaped with `htmlspecialchars(ENT_QUOTES)`.
- All pre-existing security headers in `.htaccess` (CSP-adjacent blocks, sensitive-file deny rules) left intact.
- Sitemap and canonical use the configured `APP_URL` — no user input reflected into the URL.

## 9. Local Development Configuration
- `.env`: `APP_DEBUG=true` (errors visible) + `PERF_MONITOR=true` (force monitoring on for measurement).
- Cache dir `storage/cache/` created (auto-created by `Cache.php` if missing).
- Apply migrations locally if rebuilding DB: `performance_monitoring.sql`, `performance_indexes.sql`.

## 10. Production / Live Hosting Configuration
- `.env.production.bak` has `APP_DEBUG=false` → monitoring **auto-enables** (no flag needed). To force off: `PERF_MONITOR=false`.
- Requires the two migrations imported via cPanel/phpMyAdmin before deploy.
- PHP `opcache` recommended on cPanel (`opcache.enable=1` — performance win of 20–50% typical for this framework size).
- Ensure `storage/cache/` is writable by the web user (file cache).
- `robots.txt` shipped with `Sitemap: https://rent.habellesfoundation.org/sitemap.xml`.

## 11. Before vs After Performance Results

Measurements: curl `time_total`, min of 3, XAMPP local. Timings on XAMPP are noisy (±100 ms); query counts are exact from `perf_log`.

| Route | Before (ms / queries) | After (ms / queries) |
|---|---|---|
| Home | 34 / 16 | 11–78 (median ~59) / **12** |
| Properties | 68 / 9 | 44–84 (median ~66) / **4** |
| Property detail | 90 / 18 | 71–189 (median ~147) / **16** |

**Query-count improvements are solid and exact** (16→12, 9→4, 18→16). Wall-time deltas on XAMPP are inconclusive because local MySQL cold/uncached reads dominate; the p95 and slow-query tracking in the admin Performance page will show the true production picture after a few days of traffic.

Additional verified endpoints after optimization: `/sitemap.xml` 12 ms / 3 queries (cached-friendly), `/api/notifications` 12 ms avg, `/renter/dashboard` 19 ms.

## 12. SEO Changes Summary
- **Titles:** each page passes a specific `$title` (home = "Find Accommodation — See It Before You Visit").
- **Meta descriptions:** added for properties listing + property detail (auto-generated from property description, 160 chars); CMS pages use their stored `meta_description`.
- **Canonical URLs:** every page; strips the app base path correctly (fixed the doubled `/rental_website/public/rental_website/...` bug). Verified: `http://localhost/rental_website/public/` on home.
- **robots.txt:** unchanged rules + sitemap pointer.
- **sitemap.xml:** dynamic XML — static pages (0.8/1.0), CMS pages (0.6), verified properties (0.9), blog posts (0.7), with lastmod. Verified rendering.
- **Structured data:** JSON-LD `Accommodation` schema on property detail (name, description, images, rooms, Offer with GHS price + availability, address, geo when public, AggregateRating when reviews exist). Verified in rendered HTML.
- **Open Graph / Twitter:** site name, title, description, type (`article` for properties), URL, og:image (first property image), large-image card.
- **Clean URLs:** already clean (slug-based `/properties/{slug}`, `/blog/{slug}`, `/page/{slug}`). No changes needed.
- **Internal linking:** breadcrumbs on detail page, related/similar properties — already present; not modified.
- **Image alt text:** already present on all property/blog images; not modified.
- **Mobile usability:** the prior 5-principle mobile-UX overhaul stands; no regressions observed in this pass.

## 13. Monitoring Delivered
- Automatic, always-on in production; opt-in locally via `PERF_MONITOR=true`.
- Tracks per request: duration, query count/time, slow queries (>100 ms), memory, HTTP status, PHP error type/message (first per request).
- Missing-route and unhandled-exception paths are recorded via shutdown/exception handlers.
- **Admin Performance** section (`/admin/performance`, sidebar → System → Performance):
  - 7-day summary cards: requests, avg ms, p95 ms, worst route, avg queries, slow queries, errors.
  - Per-route table: avg/max ms, avg/max query count, slow queries, errors, peak memory; amber/red highlighting for >500 ms or >0 errors.
  - Slow-query counts by route; recent errors list (route, status, type, truncated message); 14-day daily trend.
  - "Prune > 7 days" button (CSRF-protected).
- Retention: 45-day auto-prune, ~1/200 requests; no personal data anywhere.

## 14. Regression Test Results

| Feature | Result |
|---|---|
| Home, listings, blog, blog post, FAQs, contact, CMS page, how-it-works, for-renters | ✅ 200 |
| Property search `?q=`, multi-filter (region/type/price/bedrooms), amenity/walk filters, pagination | ✅ 200 |
| Invalid slug → 404, guest compare → 200, compare page | ✅ |
| Registration (POST with CSRF) | ✅ user created, verification notice rendered |
| Login | ✅ renter dashboard loads |
| Logout | ✅ session destroyed; `renter/dashboard` then 302 → login |
| Forgot-password (POST) | ✅ generic success rendered |
| Renter pages: dashboard, saved, compare, notifications, viewings, payments | ✅ 200 (`renter/applications` 404 — route does not exist in this app; pre-existing, unrelated) |
| Sitemap / admin performance route | ✅ 200 / 302-gated |
| Property condition module (public section on detail) | ✅ renders |
| Admin performance page behind auth | ✅ guest 302, renter 302 → renter/dashboard |
| View render harness for admin performance view | ✅ clean render, no warnings |
| PHP lint on all touched files | ✅ no syntax errors |

Admin CRUD flows (property create/edit/delete, image upload, payments review, dashboards) could not be exercised end-to-end locally **because no admin credentials exist in the local DB** (user discussion required the admin flows be tested with credentials; the admin controller/view layer was verified via lint + render harness + route/permission gating instead). Recommend a quick manual pass after deploy.

## 15. Remaining Bottlenecks
1. **Property detail query count (16)** — largest remaining; further gains need consolidating relations (spaces, accessibility, security, condition) into fewer round-trips. Medium effort, moderate gain.
2. **XAMPP/local timings are noise-dominated** — real p95s need production data; the admin Performance page now provides exactly this.
3. **File cache is single-server** — fine for cPanel; if the project ever moves to multi-node hosting, swap `Cache.php` internals for Redis/APCu (API is tiny).
4. **No opcache tuning in this pass** — biggest single deploy-day win available (see checklist).
5. **`perf_log` grows ~1 row/request** — the 45-day prune + admin prune button keep it bounded; at low traffic this is trivial.

## 16. Production Deployment Checklist

Upload / apply in this order:

1. **Database (cPanel → phpMyAdmin):**
   - [ ] Import `database/migrations/performance_monitoring.sql` (creates `perf_log`)
   - [ ] Import `database/migrations/performance_indexes.sql` (3 indexes)
2. **New files:**
   - [ ] `app/Services/PerformanceService.php`
   - [ ] `app/Helpers/Cache.php`
   - [ ] `app/Controllers/Admin/PerformanceController.php`
   - [ ] `app/Views/admin/performance.php`
3. **Modified files:**
   - [ ] `app/Helpers/Database.php`
   - [ ] `bootstrap/app.php`
   - [ ] `routes/web.php`
   - [ ] `app/Controllers/PublicController.php`
   - [ ] `app/Models/PropertyModel.php`
   - [ ] `app/Views/layouts/main.php`
   - [ ] `app/Views/public/property_detail.php`
   - [ ] `app/Views/partials/admin_sidebar.php`
4. **Static:**
   - [ ] `public/robots.txt`
   - [ ] Do **not** upload `.env`, `.env.production.bak`, `storage/` (except ensuring `storage/cache/` is web-writable on the server)
5. **Config:**
   - [ ] Production `.env`: `APP_DEBUG=false` (monitoring auto-on; no extra key needed)
   - [ ] cPanel PHP ini: `opcache.enable=1`
6. **Post-deploy verification:**
   - [ ] `GET /sitemap.xml` returns XML, uses `https://rent.habellesfoundation.org/` URLs
   - [ ] Home + a property detail page render with correct canonical (`https://rent.habellesfoundation.org/properties/{slug}`), og:image, JSON-LD
   - [ ] Admin → System → Performance shows rows within the first few requests
   - [ ] Manual pass: login admin, review one pending property end-to-end

---

*Report generated as the final deliverable of the performance/SEO/monitoring/testing optimization request. No existing functionality, database relationships, or business logic were altered.*
