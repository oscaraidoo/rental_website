<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\{PropertyModel, SettingsModel, HomeSlideModel, PropertyConditionModel};
use App\Helpers\{Security, Request, Paginator, Database, Str};
use App\Services\AuditService;

class PublicController extends BaseController
{
    // ── Homepage ──────────────────────────────────────────────

    public function index(): void
    {
        $featuredProperties = PropertyModel::search(['status' => 'verified', 'featured' => true], 6, 0);
        $recentProperties   = PropertyModel::search(['status' => 'verified'], 8, 0);

        // Hero slides from CMS — graceful fallback if table doesn't exist yet
        try {
            $heroSlides = HomeSlideModel::active();
        } catch (\Throwable $e) {
            $heroSlides = [];
        }

        // Reference data changes rarely — served from a short-TTL file cache
        $types   = \App\Helpers\Cache::remember('ref:types', 300,
            fn() => Database::fetchAll("SELECT id, name, icon, slug FROM property_types WHERE active=1 ORDER BY sort_order LIMIT 8"));
        $regions = \App\Helpers\Cache::remember('ref:regions', 300,
            fn() => Database::fetchAll("SELECT id, name, slug FROM regions WHERE active=1 ORDER BY name LIMIT 10"));

        // Platform stats for social proof — one query instead of three
        $statsRow = Database::fetchOne(
            "SELECT
                (SELECT COUNT(*) FROM properties WHERE verified = 1 AND deleted_at IS NULL) AS verified,
                (SELECT COUNT(*) FROM landlords) AS landlords,
                (SELECT COUNT(*) FROM viewing_requests WHERE status = 'completed') AS viewings"
        );
        $stats = [
            'verified'  => (int) ($statsRow['verified'] ?? 0),
            'landlords' => (int) ($statsRow['landlords'] ?? 0),
            'viewings'  => (int) ($statsRow['viewings'] ?? 0),
        ];

        $this->view('public.home', compact('featuredProperties', 'recentProperties', 'types', 'regions', 'stats', 'heroSlides') + [
            'title'    => 'Find Accommodation — See It Before You Visit',
            'heroPage' => true,
        ]);
    }

    // ── Property Listing / Search ─────────────────────────────

    public function properties(): void
    {
        $filters = $this->extractSearchFilters();

        $total      = PropertyModel::searchCount($filters);
        $pager      = Paginator::fromRequest($total, (int) SettingsModel::get('properties_per_page', 12));
        $properties = PropertyModel::search($filters, $pager->perPage, $pager->offset);

        // Log search for analytics
        $this->logSearch($filters, $total);

        // Reference data changes rarely — served from a short-TTL file cache
        $types      = \App\Helpers\Cache::remember('ref:types_all', 300,
            fn() => Database::fetchAll("SELECT id, name FROM property_types WHERE active=1 ORDER BY sort_order"));
        $regions    = \App\Helpers\Cache::remember('ref:regions_all', 300,
            fn() => Database::fetchAll("SELECT id, name FROM regions WHERE active=1 ORDER BY name"));
        $ceiltypes  = \App\Helpers\Cache::remember('ref:ceiltypes', 300,
            fn() => Database::fetchAll("SELECT id, name FROM ceiling_types WHERE active=1"));
        $floortypes = \App\Helpers\Cache::remember('ref:floortypes', 300,
            fn() => Database::fetchAll("SELECT id, name FROM flooring_types WHERE active=1"));
        $amenities  = \App\Helpers\Cache::remember('ref:amenities', 300,
            fn() => Database::fetchAll("SELECT id, name, category FROM amenities WHERE active=1 ORDER BY category, sort_order"));

        // City list for selected region
        $cities = [];
        if (!empty($filters['region_id'])) {
            $cities = Database::fetchAll(
                "SELECT id, name FROM cities WHERE region_id=? AND active=1 ORDER BY name",
                [$filters['region_id']]
            );
        }

        $this->view('public.properties', compact(
            'properties', 'filters', 'pager', 'total',
            'types', 'regions', 'cities', 'ceiltypes', 'floortypes', 'amenities'
        ) + [
            'title'           => 'Search Properties',
            'metaDescription' => 'Browse verified rental properties across Ghana — filter by region, price, bedrooms, walking time and vehicle access before you visit.',
        ]);
    }

    public function search(): void
    {
        // Redirect GET search to properties page
        $this->properties();
    }

    // ── Property Detail ───────────────────────────────────────

    public function propertyDetail(string $slug): void
    {
        $property = PropertyModel::findBySlug($slug);

        if (!$property || $property['status'] !== 'verified') {
            $this->notFound();
        }

        // Some PDO builds (libmysqlclient without mysqlnd) return numeric columns
        // as strings — normalize the id so strict int-typed model methods never
        // throw a TypeError.
        $propertyId = (int) $property['id'];

        // Newest active condition report (admin-assessed)
        $conditionReport = PropertyConditionModel::activeForProperty($propertyId);

        // Increment view count (deduplicate by session)
        $viewKey = 'viewed_property_' . $propertyId;
        if (empty($_SESSION[$viewKey])) {
            PropertyModel::incrementViewCount($propertyId);
            $_SESSION[$viewKey] = true;

            // Log to property_views table
            Database::insert('property_views', [
                'property_id' => $propertyId,
                'user_id'     => $_SESSION['user_id'] ?? null,
                'ip_address'  => Security::getIp(),
                'session_id'  => session_id(),
                'source'      => $_SERVER['HTTP_REFERER'] ?? 'direct',
            ]);
        }

        // Is saved by current user?
        $isSaved = false;
        if (!empty($_SESSION['user_id'])) {
            $isSaved = PropertyModel::isSavedByUser($propertyId, (int) $_SESSION['user_id']);
        }

        // Is in compare list?
        $inCompare = false;
        if (!empty($_SESSION['user_id'])) {
            $inCompare = (bool) Database::fetchColumn(
                "SELECT id FROM property_comparisons WHERE property_id=? AND user_id=?",
                [$propertyId, $_SESSION['user_id']]
            );
        }

        // Viewing fee
        $viewingFee = \App\Models\ViewingModel::getViewingFee($propertyId);

        // Landlord contact unlock (renters pay to view)
        $unlockFee      = \App\Controllers\Renter\LandlordContactController::unlockFee();
        $contactUnlocked = false;
        if ($isRenter = (!empty($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'renter')) {
            $contactUnlocked = \App\Controllers\Renter\LandlordContactController::isUnlocked($propertyId, (int) $_SESSION['user_id']);
        }

        // Approved reviews + average ratings — one query instead of two
        $reviews = Database::fetchAll(
            "SELECT r.*, CONCAT(u.first_name,' ',SUBSTRING(u.last_name,1,1),'.') AS reviewer_name,
                    (SELECT AVG(rating_overall)   FROM reviews WHERE property_id = ? AND status = 'approved') AS avg_overall,
                    (SELECT AVG(rating_accuracy)  FROM reviews WHERE property_id = ? AND status = 'approved') AS avg_accuracy,
                    (SELECT AVG(rating_condition) FROM reviews WHERE property_id = ? AND status = 'approved') AS avg_condition,
                    (SELECT AVG(rating_location)  FROM reviews WHERE property_id = ? AND status = 'approved') AS avg_location,
                    (SELECT COUNT(*)              FROM reviews WHERE property_id = ? AND status = 'approved') AS review_total
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.property_id = ? AND r.status = 'approved'
             ORDER BY r.created_at DESC LIMIT 10",
            [$propertyId, $propertyId, $propertyId, $propertyId, $propertyId, $propertyId]
        );

        // Average ratings (computed from the aggregate columns on any review row;
        // separate rows kept for view compatibility)
        $avgRatings = $reviews
            ? [
                'avg_overall'   => $reviews[0]['avg_overall']   !== null ? round((float) $reviews[0]['avg_overall'], 2)   : null,
                'avg_accuracy'  => $reviews[0]['avg_accuracy']  !== null ? round((float) $reviews[0]['avg_accuracy'], 2)  : null,
                'avg_condition' => $reviews[0]['avg_condition'] !== null ? round((float) $reviews[0]['avg_condition'], 2) : null,
                'avg_location'  => $reviews[0]['avg_location']  !== null ? round((float) $reviews[0]['avg_location'], 2)  : null,
                'total'         => (int) ($reviews[0]['review_total'] ?? 0),
              ]
            : [
                'avg_overall' => null, 'avg_accuracy' => null,
                'avg_condition' => null, 'avg_location' => null, 'total' => 0,
            ];

        // Similar properties (same city, similar type)
        $similar = PropertyModel::search([
            'status'  => 'verified',
            'city_id' => $property['location']['city_id'] ?? 0,
            'type_id' => $property['property_type_id'],
        ], 4, 0);
        $similar = array_filter($similar, fn($p) => (int) $p['id'] !== $propertyId);

        $this->view('public.property_detail', compact(
            'property', 'isSaved', 'inCompare', 'viewingFee', 'reviews', 'avgRatings', 'similar', 'conditionReport',
            'unlockFee', 'contactUnlocked'
        ) + [
            'title'           => $property['title'] . ' — ' . ($property['city_name'] ?? '') . ' | Rental',
            'metaDescription' => Str::truncate($property['description'], 160),
            'ogType'          => 'article',
            'ogImage'         => !empty($property['images'][0]['path'])
                ? \App\Helpers\Url::image($property['images'][0]['path'])
                : null,
        ]);
    }

    // ── Track property view (AJAX) ────────────────────────────

    public function trackView(string $id): void
    {
        if (!Request::isAjax()) $this->notFound();

        $prop = PropertyModel::findById((int) $id);
        if ($prop && $prop['status'] === 'verified') {
            $key = 'viewed_property_' . $id;
            if (empty($_SESSION[$key])) {
                PropertyModel::incrementViewCount((int) $id);
                $_SESSION[$key] = true;
            }
        }
        $this->jsonSuccess(null);
    }

    // ── Compare (public, session-based for guests) ────────────

    public function compare(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('renter/compare');
        }

        // Guest compare uses session
        $ids = $_SESSION['compare_ids'] ?? [];
        $properties = [];
        foreach (array_unique(array_slice($ids, 0, 4)) as $id) {
            $p = PropertyModel::findById((int) $id);
            if ($p && $p['status'] === 'verified') {
                // Enrich with related data for comparison
                $p['accessibility'] = PropertyModel::getAccessibility((int) $id);
                $p['security']      = PropertyModel::getSecurity((int) $id);
                $properties[]       = $p;
            }
        }

        $this->view('public.compare', compact('properties') + ['title' => 'Compare Properties']);
    }

    public function compareAdd(): void
    {
        Security::requireCsrf();
        $propertyId = (int) Request::post('property_id', 0);

        if (!empty($_SESSION['user_id'])) {
            $added = PropertyModel::addToCompare($propertyId, (int) $_SESSION['user_id']);
            $count = (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM property_comparisons WHERE user_id=?", [$_SESSION['user_id']]
            );
        } else {
            // Guest: use session
            $ids = $_SESSION['compare_ids'] ?? [];
            if (!in_array($propertyId, $ids) && count($ids) < 4) {
                $ids[] = $propertyId;
                $_SESSION['compare_ids'] = $ids;
                $added = true;
            } else {
                $added = false;
            }
            $count = count($_SESSION['compare_ids'] ?? []);
        }

        if (Request::isAjax()) {
            $this->jsonSuccess(['added' => $added, 'count' => $count], $added ? 'Added to compare.' : 'Max 4 properties.');
        }
        $this->back();
    }

    public function compareRemove(): void
    {
        Security::requireCsrf();
        $propertyId = (int) Request::post('property_id', 0);

        if (!empty($_SESSION['user_id'])) {
            PropertyModel::removeFromCompare($propertyId, (int) $_SESSION['user_id']);
            $count = (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM property_comparisons WHERE user_id=?", [$_SESSION['user_id']]
            );
        } else {
            $ids = array_filter($_SESSION['compare_ids'] ?? [], fn($id) => $id !== $propertyId);
            $_SESSION['compare_ids'] = array_values($ids);
            $count = count($_SESSION['compare_ids']);
        }

        if (Request::isAjax()) {
            $this->jsonSuccess(['count' => $count], 'Removed from compare.');
        }
        $this->back();
    }

    // ── CMS Pages ─────────────────────────────────────────────

    public function cmsPage(string $slug): void
    {
        $page = Database::fetchOne(
            "SELECT * FROM cms_pages WHERE slug=? AND status='published'", [$slug]
        );
        if (!$page) $this->notFound();

        $this->view('public.cms_page', compact('page') + [
            'title'           => $page['title'],
            'metaDescription' => $page['meta_description'] ?? '',
        ]);
    }

    public function about(): void    { $this->cmsPage('about'); }

    public function howItWorks(): void
    {
        $page = Database::fetchOne(
            "SELECT * FROM cms_pages WHERE slug='how-it-works' AND status='published'"
        );
        $this->view('public.how_it_works', [
            'page'  => $page ?? ['title' => 'How It Works'],
            'title' => 'How It Works — RentalWebsite',
        ]);
    }

    public function forRenters(): void
    {
        $page = Database::fetchOne(
            "SELECT * FROM cms_pages WHERE slug='for-renters' AND status='published'"
        );
        $this->view('public.for_renters', [
            'page'  => $page ?? ['title' => 'For Renters'],
            'title' => 'For Renters — RentalWebsite',
        ]);
    }

    public function forLandlords(): void { $this->cmsPage('for-landlords'); }

    // ── Blog ──────────────────────────────────────────────────

    public function blog(): void
    {
        $posts = Database::fetchAll(
            "SELECT bp.id, bp.title, bp.slug, bp.excerpt, bp.featured_image,
                    bp.category, bp.published_at, bp.view_count,
                    CONCAT(u.first_name,' ',u.last_name) AS author_name
             FROM blog_posts bp JOIN users u ON u.id = bp.author_id
             WHERE bp.status='published'
             ORDER BY bp.published_at DESC LIMIT 12"
        );

        $this->view('public.blog', compact('posts') + ['title' => 'Blog & News']);
    }

    public function blogPost(string $slug): void
    {
        $post = Database::fetchOne(
            "SELECT bp.*, CONCAT(u.first_name,' ',u.last_name) AS author_name
             FROM blog_posts bp JOIN users u ON u.id = bp.author_id
             WHERE bp.slug=? AND bp.status='published'",
            [$slug]
        );
        if (!$post) $this->notFound();

        // Increment view
        Database::query("UPDATE blog_posts SET view_count=view_count+1 WHERE id=?", [$post['id']]);

        $this->view('public.blog_post', compact('post') + [
            'title'           => $post['title'],
            'metaDescription' => $post['meta_description'] ?? Str::truncate(strip_tags($post['excerpt'] ?? ''), 160),
        ]);
    }

    // ── FAQs ──────────────────────────────────────────────────

    public function faqs(): void
    {
        $faqs = Database::fetchAll(
            "SELECT * FROM faqs WHERE active=1 ORDER BY category, sort_order"
        );
        $grouped = [];
        foreach ($faqs as $faq) {
            $grouped[$faq['category']][] = $faq;
        }

        $this->view('public.faqs', compact('grouped') + ['title' => 'Frequently Asked Questions']);
    }

    // ── Contact ───────────────────────────────────────────────

    public function contact(): void
    {
        $this->view('public.contact', ['title' => 'Contact Us']);
    }

    public function contactSubmit(): void
    {
        Security::requireCsrf();

        $data = [
            'name'    => Security::sanitize(Request::post('name', '')),
            'email'   => Security::sanitizeEmail(Request::post('email', '')),
            'phone'   => Security::sanitize(Request::post('phone', '')),
            'subject' => Security::sanitize(Request::post('subject', '')),
            'message' => Security::sanitize(Request::post('message', '')),
            'type'    => Security::sanitize(Request::post('type', 'general')),
        ];

        if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
            $this->error('Please fill in all required fields.');
            $this->redirect('contact');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error('Please enter a valid email address.');
            $this->redirect('contact');
        }

        // Rate limit: 3 messages per IP per hour
        $rlKey = Security::getIp() . ':contact';
        if (Security::isRateLimited($rlKey, 3, 60)) {
            $this->error('Too many submissions. Please try again later.');
            $this->redirect('contact');
        }
        Security::recordAttempt($rlKey, 3, 60);

        Database::insert('contact_messages', array_merge($data, [
            'ip_address' => Security::getIp(),
        ]));

        $this->success('Thank you! Your message has been sent. We\'ll get back to you soon.');
        $this->redirect('contact');
    }

    // ── Sitemap (search engines) ──────────────────────────────

    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=UTF-8');

        $base = rtrim(\App\Helpers\Url::base(), '/');
        $urls = [];

        // Static public pages
        foreach (['', 'properties', 'how-it-works', 'for-renters', 'for-landlords', 'blog', 'faqs', 'contact'] as $p) {
            $urls[] = ['loc' => $base . '/' . $p, 'lastmod' => null, 'priority' => $p === '' ? '1.0' : '0.8'];
        }

        // Published CMS pages
        foreach (Database::fetchAll("SELECT slug, updated_at FROM cms_pages WHERE status='published'") as $row) {
            $urls[] = ['loc' => $base . '/page/' . $row['slug'], 'lastmod' => substr((string) $row['updated_at'], 0, 10), 'priority' => '0.6'];
        }

        // Verified properties
        foreach (Database::fetchAll(
            "SELECT slug, COALESCE(updated_at, created_at) AS lastmod
             FROM properties
             WHERE status='verified' AND deleted_at IS NULL
             ORDER BY created_at DESC LIMIT 5000"
        ) as $row) {
            $urls[] = ['loc' => $base . '/properties/' . $row['slug'], 'lastmod' => substr((string) $row['lastmod'], 0, 10), 'priority' => '0.9'];
        }

        // Published blog posts
        foreach (Database::fetchAll(
            "SELECT slug, COALESCE(updated_at, published_at) AS lastmod
             FROM blog_posts WHERE status='published' ORDER BY published_at DESC LIMIT 2000"
        ) as $row) {
            $urls[] = ['loc' => $base . '/blog/' . $row['slug'], 'lastmod' => substr((string) $row['lastmod'], 0, 10), 'priority' => '0.7'];
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            echo "  <url>\n";
            echo '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            if ($u['lastmod']) echo '    <lastmod>' . htmlspecialchars($u['lastmod'], ENT_XML1) . "</lastmod>\n";
            echo '    <priority>' . $u['priority'] . "</priority>\n";
            echo "  </url>\n";
        }
        echo '</urlset>' . "\n";
        exit;
    }

    // ── Helpers ───────────────────────────────────────────────

    private function extractSearchFilters(): array
    {
        return [
            'q'             => Security::sanitize(Request::get('q', '')),
            'region_id'     => Security::sanitizeInt(Request::get('region_id', 0)) ?: null,
            'city_id'       => Security::sanitizeInt(Request::get('city_id', 0)) ?: null,
            'area_id'       => Security::sanitizeInt(Request::get('area_id', 0)) ?: null,
            'type_id'       => Security::sanitizeInt(Request::get('type_id', 0)) ?: null,
            'price_min'     => Security::sanitizeFloat(Request::get('price_min', '')) ?: null,
            'price_max'     => Security::sanitizeFloat(Request::get('price_max', '')) ?: null,
            'bedrooms'      => Request::get('bedrooms', ''),
            'bathrooms'     => Security::sanitizeInt(Request::get('bathrooms', 0)) ?: null,
            'self_contained'=> Request::get('self_contained', ''),
            'parking'       => Request::get('parking', ''),
            'water'         => Request::get('water', ''),
            'furnished'     => Request::get('furnished', ''),
            'gated'         => Request::get('gated', ''),
            'pets'          => Request::get('pets', ''),
            'electricity'   => Request::get('electricity', ''),
            'walk_max'      => Security::sanitizeInt(Request::get('walk_max', 0)) ?: null,
            'vehicle_access'=> Request::get('vehicle_access', ''),
            'ceiling_type'  => Security::sanitizeInt(Request::get('ceiling_type', 0)) ?: null,
            'flooring_type' => Security::sanitizeInt(Request::get('flooring_type', 0)) ?: null,
            'building_level'=> Request::get('building_level', ''),
            'amenity_ids'   => Request::get('amenity_ids', []),
            'status'        => 'verified',
        ];
    }

    private function logSearch(array $filters, int $results): void
    {
        $query = $filters['q'] ?? '';
        $logFilters = array_filter($filters, fn($v) => $v !== null && $v !== '' && $v !== [] && $v !== 'verified');
        Database::insert('search_logs', [
            'user_id'       => $_SESSION['user_id'] ?? null,
            'query'         => $query ?: null,
            'filters'       => json_encode($logFilters),
            'results_count' => $results,
            'ip_address'    => Security::getIp(),
        ]);
    }
}
