<?php
/**
 * Web Router
 * Simple pattern-based router. All routes defined here.
 */

declare(strict_types=1);

use App\Helpers\{Request, Response, Url};

// ── Resolve current path ────────────────────────────────────
$requestUri = Request::uri();
$basePath   = parse_url((require ROOT_PATH . '/config/app.php')['url'], PHP_URL_PATH) ?? '';

// Strip base path prefix
if ($basePath && str_starts_with($requestUri, $basePath)) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = '/' . trim($requestUri, '/');
$method     = Request::method();

// Strip query string
$path = explode('?', $requestUri)[0];
$path = '/' . trim($path, '/');

// Performance monitoring: record which route handled this request
\App\Services\PerformanceService::setRoute($path);

// ── Route definitions ───────────────────────────────────────
// Format: [METHOD, pattern, ControllerClass::method]
// Use :param for named segments, * for wildcard

$routes = [
    // ── Public pages ──────────────────────────────────────────
    ['GET',  '/',                           'App\Controllers\PublicController@index'],
    ['GET',  '/properties',                 'App\Controllers\PublicController@properties'],
    ['GET',  '/properties/:slug',           'App\Controllers\PublicController@propertyDetail'],
    ['GET',  '/search',                     'App\Controllers\PublicController@search'],
    ['GET',  '/compare',                    'App\Controllers\PublicController@compare'],
    ['POST', '/compare/add',                'App\Controllers\PublicController@compareAdd'],
    ['POST', '/compare/remove',             'App\Controllers\PublicController@compareRemove'],
    ['GET',  '/about',                      'App\Controllers\PublicController@about'],
    ['GET',  '/how-it-works',               'App\Controllers\PublicController@howItWorks'],
    ['GET',  '/for-landlords',              'App\Controllers\PublicController@forLandlords'],
    ['GET',  '/for-renters',                'App\Controllers\PublicController@forRenters'],
    ['GET',  '/contact',                    'App\Controllers\PublicController@contact'],
    ['POST', '/contact',                    'App\Controllers\PublicController@contactSubmit'],
    ['GET',  '/page/:slug',                 'App\Controllers\PublicController@cmsPage'],
    ['GET',  '/blog',                       'App\Controllers\PublicController@blog'],
    ['GET',  '/blog/:slug',                 'App\Controllers\PublicController@blogPost'],
    ['GET',  '/faqs',                       'App\Controllers\PublicController@faqs'],
    ['GET',  '/sitemap.xml',                'App\Controllers\PublicController@sitemap'],

    // ── Authentication ────────────────────────────────────────
    ['GET',  '/auth/login',                 'App\Controllers\Auth\LoginController@showLogin'],
    ['POST', '/auth/login',                 'App\Controllers\Auth\LoginController@login'],
    ['GET',  '/auth/logout',                'App\Controllers\Auth\LoginController@logout'],
    ['GET',  '/auth/register',              'App\Controllers\Auth\RegisterController@showRegister'],
    ['GET',  '/auth/google',                'App\Controllers\Auth\GoogleAuthController@redirectToGoogle'],
    ['GET',  '/auth/google/callback',       'App\Controllers\Auth\GoogleAuthController@callback'],
    ['POST', '/auth/register',              'App\Controllers\Auth\RegisterController@register'],
    ['GET',  '/auth/verify-email',          'App\Controllers\Auth\RegisterController@showVerify'],
    ['POST', '/auth/verify-email',          'App\Controllers\Auth\RegisterController@verifyOtp'],
    ['GET',  '/auth/verify/:token',         'App\Controllers\Auth\RegisterController@verifyEmail'],
    ['POST', '/auth/resend-verification',   'App\Controllers\Auth\RegisterController@resendVerification'],
    ['GET',  '/auth/forgot-password',       'App\Controllers\Auth\PasswordController@showForgot'],
    ['POST', '/auth/forgot-password',       'App\Controllers\Auth\PasswordController@sendReset'],
    ['GET',  '/auth/reset-password/:token', 'App\Controllers\Auth\PasswordController@showReset'],
    ['POST', '/auth/reset-password',        'App\Controllers\Auth\PasswordController@resetPassword'],

    // ── Admin ─────────────────────────────────────────────────
    ['GET',  '/admin',                              'App\Controllers\Admin\DashboardController@index'],
    ['GET',  '/admin/dashboard',                    'App\Controllers\Admin\DashboardController@index'],
    // Performance monitoring (admin, no personal data)
    ['GET',  '/admin/performance',                 'App\Controllers\Admin\PerformanceController@index'],
    ['POST', '/admin/performance/clear',           'App\Controllers\Admin\PerformanceController@clear'],
    ['GET',  '/admin/notifications',               'App\Controllers\Admin\NotificationController@index'],
    ['POST', '/admin/notifications/:id/read',      'App\Controllers\Admin\NotificationController@markRead'],
    ['POST', '/admin/notifications/read-all',      'App\Controllers\Admin\NotificationController@markAllRead'],
    ['POST', '/admin/notifications/:id/delete',    'App\Controllers\Admin\NotificationController@destroy'],
    // Outbound (email/SMS) notification log
    ['GET',  '/admin/notifications/log',           'App\Controllers\Admin\NotificationLogController@index'],
    ['POST', '/admin/notifications/log/:id/retry', 'App\Controllers\Admin\NotificationLogController@retry'],
    // Users
    ['GET',  '/admin/users',                        'App\Controllers\Admin\UserController@index'],
    ['GET',  '/admin/users/create',                 'App\Controllers\Admin\UserController@create'],
    ['POST', '/admin/users/create',                 'App\Controllers\Admin\UserController@store'],
    ['GET',  '/admin/users/:id/edit',               'App\Controllers\Admin\UserController@edit'],
    ['POST', '/admin/users/:id/update',             'App\Controllers\Admin\UserController@update'],
    ['POST', '/admin/users/:id/suspend',            'App\Controllers\Admin\UserController@suspend'],
    ['POST', '/admin/users/:id/activate',           'App\Controllers\Admin\UserController@activate'],
    ['POST', '/admin/users/:id/delete',             'App\Controllers\Admin\UserController@destroy'],
    // Properties
    ['GET',  '/admin/properties',                   'App\Controllers\Admin\PropertyController@index'],
    ['GET',  '/admin/properties/create',            'App\Controllers\Admin\PropertyController@create'],
    ['POST', '/admin/properties/create',            'App\Controllers\Admin\PropertyController@store'],
    ['GET',  '/admin/properties/:id/edit',          'App\Controllers\Admin\PropertyController@edit'],
    ['POST', '/admin/properties/:id/update',        'App\Controllers\Admin\PropertyController@update'],
    ['POST', '/admin/properties/:id/images',        'App\Controllers\Admin\PropertyController@addImage'],
    ['POST', '/admin/properties/:id/images/import', 'App\Controllers\Admin\PropertyController@importImage'],
    ['POST', '/admin/properties/images/:imageId/primary', 'App\Controllers\Admin\PropertyController@setPrimaryImage'],
    ['POST', '/admin/properties/images/:imageId/delete',  'App\Controllers\Admin\PropertyController@deleteImage'],
    // Property condition assessments
    ['POST', '/admin/properties/:id/conditions',            'App\Controllers\Admin\PropertyController@storeCondition'],
    ['POST', '/admin/properties/:id/conditions/:conditionId/update', 'App\Controllers\Admin\PropertyController@updateCondition'],
    ['POST', '/admin/properties/:id/conditions/:conditionId/delete', 'App\Controllers\Admin\PropertyController@deleteCondition'],
    ['POST', '/admin/properties/:id/conditions/:conditionId/photos/:index/delete', 'App\Controllers\Admin\PropertyController@deleteConditionPhoto'],
    ['GET',  '/admin/properties/:id',               'App\Controllers\Admin\PropertyController@show'],
    ['POST', '/admin/properties/:id/approve',       'App\Controllers\Admin\PropertyController@approve'],
    ['POST', '/admin/properties/:id/landlord-contact', 'App\Controllers\Admin\PropertyController@saveLandlordContact'],
    ['POST', '/admin/properties/:id/reject',        'App\Controllers\Admin\PropertyController@reject'],
    ['POST', '/admin/properties/:id/verify',        'App\Controllers\Admin\PropertyController@verify'],
    ['POST', '/admin/properties/:id/feature',       'App\Controllers\Admin\PropertyController@feature'],
    ['POST', '/admin/properties/:id/suspend',       'App\Controllers\Admin\PropertyController@suspend'],
    ['POST', '/admin/properties/:id/delete',        'App\Controllers\Admin\PropertyController@delete'],
    // Verification
    ['GET',  '/admin/verifications',                'App\Controllers\Admin\VerificationController@index'],
    ['GET',  '/admin/verifications/:id',            'App\Controllers\Admin\VerificationController@show'],
    ['POST', '/admin/verifications/:id/complete',   'App\Controllers\Admin\VerificationController@complete'],
    // Viewings
    ['GET',  '/admin/viewings',                     'App\Controllers\Admin\ViewingController@index'],
    ['GET',  '/admin/viewings/:id',                 'App\Controllers\Admin\ViewingController@show'],
    ['POST', '/admin/viewings/:id/confirm',         'App\Controllers\Admin\ViewingController@confirm'],
    ['POST', '/admin/viewings/:id/cancel',          'App\Controllers\Admin\ViewingController@cancel'],
    ['POST', '/admin/viewings/:id/delete',          'App\Controllers\Admin\ViewingController@destroy'],
    // Payments
    ['GET',  '/admin/payments',                     'App\Controllers\Admin\PaymentController@index'],
    ['GET',  '/admin/payments/:id',                 'App\Controllers\Admin\PaymentController@show'],
    // Reports
    ['GET',  '/admin/reports',                      'App\Controllers\Admin\ReportController@index'],
    ['GET',  '/admin/reports/properties',           'App\Controllers\Admin\ReportController@properties'],
    ['GET',  '/admin/reports/revenue',              'App\Controllers\Admin\ReportController@revenue'],
    ['GET',  '/admin/reports/viewings',             'App\Controllers\Admin\ReportController@viewings'],
    ['GET',  '/admin/reports/export/:type',         'App\Controllers\Admin\ReportController@export'],
    // Settings
    ['GET',  '/admin/settings',                     'App\Controllers\Admin\SettingsController@index'],
    ['POST', '/admin/settings',                     'App\Controllers\Admin\SettingsController@update'],
    ['GET',  '/admin/settings/fees',                'App\Controllers\Admin\SettingsController@fees'],
    ['POST', '/admin/settings/fees',                'App\Controllers\Admin\SettingsController@updateFees'],
    // CMS
    ['GET',  '/admin/cms',                          'App\Controllers\Admin\CmsController@index'],
    // Home page hero slides (images & videos)
    ['GET',  '/admin/cms/home',                     'App\Controllers\Admin\CmsController@homeSlides'],
    ['POST', '/admin/cms/home/store',               'App\Controllers\Admin\CmsController@homeSlideStore'],
    ['POST', '/admin/cms/home/:id/update',          'App\Controllers\Admin\CmsController@homeSlideUpdate'],
    ['POST', '/admin/cms/home/:id/delete',          'App\Controllers\Admin\CmsController@homeSlideDelete'],
    ['POST', '/admin/cms/home/:id/toggle',          'App\Controllers\Admin\CmsController@homeSlideToggle'],
    ['POST', '/admin/cms/home/:id/move/:dir',       'App\Controllers\Admin\CmsController@homeSlideMove'],
    ['GET',  '/admin/cms/:slug/edit',               'App\Controllers\Admin\CmsController@edit'],
    ['POST', '/admin/cms/:slug/update',             'App\Controllers\Admin\CmsController@update'],
    ['GET',  '/admin/faqs',                         'App\Controllers\Admin\CmsController@faqs'],
    ['POST', '/admin/faqs/store',                   'App\Controllers\Admin\CmsController@storeFaq'],
    ['POST', '/admin/faqs/:id/update',              'App\Controllers\Admin\CmsController@updateFaq'],
    ['POST', '/admin/faqs/:id/delete',              'App\Controllers\Admin\CmsController@deleteFaq'],
    ['GET',  '/admin/blog',                         'App\Controllers\Admin\CmsController@blog'],
    ['GET',  '/admin/blog/create',                  'App\Controllers\Admin\CmsController@createBlog'],
    ['POST', '/admin/blog/store',                   'App\Controllers\Admin\CmsController@storeBlog'],
    ['GET',  '/admin/blog/:id/edit',                'App\Controllers\Admin\CmsController@editBlog'],
    ['POST', '/admin/blog/:id/update',              'App\Controllers\Admin\CmsController@updateBlog'],
    ['POST', '/admin/blog/:id/delete',              'App\Controllers\Admin\CmsController@deleteBlog'],
    // Audit log
    ['GET',  '/admin/audit-logs',                   'App\Controllers\Admin\AuditController@index'],
    // Complaints
    ['GET',  '/admin/complaints',                   'App\Controllers\Admin\ComplaintController@index'],
    ['GET',  '/admin/complaints/:id',               'App\Controllers\Admin\ComplaintController@show'],
    ['POST', '/admin/complaints/:id/resolve',       'App\Controllers\Admin\ComplaintController@resolve'],
    // Locations
    ['GET',  '/admin/locations',                    'App\Controllers\Admin\LocationController@index'],
    ['POST', '/admin/locations/region',             'App\Controllers\Admin\LocationController@storeRegion'],
    ['POST', '/admin/locations/city',               'App\Controllers\Admin\LocationController@storeCity'],
    ['POST', '/admin/locations/area',               'App\Controllers\Admin\LocationController@storeArea'],
    ['POST', '/admin/locations/region/:id/delete',  'App\Controllers\Admin\LocationController@deleteRegion'],
    ['POST', '/admin/locations/city/:id/delete',    'App\Controllers\Admin\LocationController@deleteCity'],
    ['POST', '/admin/locations/area/:id/delete',    'App\Controllers\Admin\LocationController@deleteArea'],
    // Property Types & Amenities
    ['GET',  '/admin/property-types',               'App\Controllers\Admin\PropertyTypeController@index'],
    ['POST', '/admin/property-types/store',         'App\Controllers\Admin\PropertyTypeController@store'],
    ['POST', '/admin/property-types/:id/update',    'App\Controllers\Admin\PropertyTypeController@update'],
    ['POST', '/admin/property-types/:id/delete',    'App\Controllers\Admin\PropertyTypeController@destroy'],
    ['GET',  '/admin/amenities',                    'App\Controllers\Admin\AmenityController@index'],
    ['POST', '/admin/amenities/store',              'App\Controllers\Admin\AmenityController@store'],
    ['POST', '/admin/amenities/:id/update',         'App\Controllers\Admin\AmenityController@update'],
    ['POST', '/admin/amenities/:id/delete',         'App\Controllers\Admin\AmenityController@destroy'],
    // Agreements
    ['GET',  '/admin/agreements',                   'App\Controllers\Admin\AgreementController@index'],
    ['GET',  '/admin/agreements/:id',               'App\Controllers\Admin\AgreementController@show'],

    // ── Renter ────────────────────────────────────────────────
    ['GET',  '/renter',                             'App\Controllers\Renter\DashboardController@index'],
    ['GET',  '/renter/dashboard',                   'App\Controllers\Renter\DashboardController@index'],
    ['GET',  '/renter/profile',                     'App\Controllers\Renter\ProfileController@show'],
    ['POST', '/renter/profile',                     'App\Controllers\Renter\ProfileController@update'],
    ['POST', '/renter/profile/photo',               'App\Controllers\Renter\ProfileController@updatePhoto'],
    ['POST', '/renter/profile/photo/remove',        'App\Controllers\Renter\ProfileController@removePhoto'],
    ['GET',  '/renter/profile/security',            'App\Controllers\Renter\ProfileController@security'],
    ['POST', '/renter/profile/password',            'App\Controllers\Renter\ProfileController@changePassword'],
    ['GET',  '/renter/profile/settings',            'App\Controllers\Renter\ProfileController@settings'],
    ['POST', '/renter/profile/settings',            'App\Controllers\Renter\ProfileController@saveSettings'],
    ['GET',  '/renter/profile/account',             'App\Controllers\Renter\ProfileController@account'],
    ['POST', '/renter/profile/deactivate',          'App\Controllers\Renter\ProfileController@deactivate'],
    ['POST', '/renter/profile/resend-verification', 'App\Controllers\Renter\ProfileController@resendVerification'],
    // Saved
    ['GET',  '/renter/saved',                       'App\Controllers\Renter\SavedController@index'],
    ['POST', '/renter/saved/toggle',                'App\Controllers\Renter\SavedController@toggle'],
    ['POST', '/renter/saved/:id/remove',            'App\Controllers\Renter\SavedController@remove'],
    // Compare
    ['GET',  '/renter/compare',                     'App\Controllers\Renter\CompareController@index'],
    // Viewing requests
    ['GET',  '/renter/viewings',                    'App\Controllers\Renter\ViewingController@index'],
    ['GET',  '/renter/viewings/request/:propertyId','App\Controllers\Renter\ViewingController@requestForm'],
    ['POST', '/renter/viewings/request',            'App\Controllers\Renter\ViewingController@submitRequest'],
    ['GET',  '/renter/viewings/:id',                'App\Controllers\Renter\ViewingController@show'],
    ['POST', '/renter/viewings/:id/cancel',         'App\Controllers\Renter\ViewingController@cancel'],
    ['POST', '/renter/viewings/:id/pay',            'App\Controllers\Renter\ViewingController@initiatePayment'],
    ['GET',  '/renter/viewings/:id/payment-return', 'App\Controllers\Renter\ViewingController@paymentReturn'],
    // Reviews
    ['GET',  '/renter/reviews/create/:viewingId',   'App\Controllers\Renter\ReviewController@create'],
    ['POST', '/renter/reviews/store',               'App\Controllers\Renter\ReviewController@store'],
    // Reports
    ['GET',  '/renter/reports/create/:propertyId',  'App\Controllers\Renter\ReportController@create'],
    ['POST', '/renter/reports/store',               'App\Controllers\Renter\ReportController@store'],
    // Notifications
    ['GET',  '/renter/notifications',               'App\Controllers\Renter\NotificationController@index'],
    ['POST', '/renter/notifications/:id/read',      'App\Controllers\Renter\NotificationController@markRead'],
    ['POST', '/renter/notifications/read-all',      'App\Controllers\Renter\NotificationController@markAllRead'],
    ['POST', '/renter/notifications/:id/delete',    'App\Controllers\Renter\NotificationController@destroy'],
    ['GET',  '/renter/notifications/settings',      'App\Controllers\Renter\NotificationController@settings'],
    ['POST', '/renter/notifications/settings',      'App\Controllers\Renter\NotificationController@saveSettings'],
    // Payments
    ['GET',  '/renter/payments',                    'App\Controllers\Renter\PaymentController@index'],
    // Landlord contact unlock
    ['POST', '/renter/landlord-contact/unlock/:propertyId', 'App\Controllers\Renter\LandlordContactController@unlock'],
    ['GET',  '/renter/landlord-contact/view/:propertyId',   'App\Controllers\Renter\LandlordContactController@show'],

    // ── API ───────────────────────────────────────────────────
    ['GET',  '/api/properties',                     'App\Controllers\Api\PropertyApiController@index'],
    ['GET',  '/api/properties/:id',                 'App\Controllers\Api\PropertyApiController@show'],
    ['GET',  '/api/search',                         'App\Controllers\Api\PropertyApiController@search'],
    ['POST', '/api/properties/:id/favorite',        'App\Controllers\Api\PropertyApiController@toggleFavorite'],
    ['GET',  '/api/notifications',                  'App\Controllers\Api\NotificationApiController@index'],
    ['POST', '/api/notifications/:id/read',         'App\Controllers\Api\NotificationApiController@markRead'],
    ['POST', '/api/notifications/read-all',         'App\Controllers\Api\NotificationApiController@markAllRead'],
    ['POST', '/api/notifications/:id/delete',       'App\Controllers\Api\NotificationApiController@destroy'],
    // Notification settings / test / history API
    ['GET',  '/api/notification-settings',          'App\Controllers\Api\NotificationSettingsApiController@show'],
    ['POST', '/api/notification-settings',          'App\Controllers\Api\NotificationSettingsApiController@update'],
    ['POST', '/api/notification-settings/test',     'App\Controllers\Api\NotificationSettingsApiController@test'],
    ['GET',  '/api/notification-history',           'App\Controllers\Api\NotificationSettingsApiController@history'],
    ['GET',  '/api/viewings',                       'App\Controllers\Api\ViewingApiController@index'],
    ['GET',  '/api/locations',                      'App\Controllers\Api\LocationApiController@index'],
    ['GET',  '/api/regions',                        'App\Controllers\Api\LocationApiController@regions'],
    ['GET',  '/api/cities/:regionId',               'App\Controllers\Api\LocationApiController@cities'],
    ['GET',  '/api/areas/:cityId',                  'App\Controllers\Api\LocationApiController@areas'],

    // ── Property view tracking (AJAX) ─────────────────────────
    ['POST', '/track/view/:id',                     'App\Controllers\PublicController@trackView'],
];

// ── Route dispatcher ────────────────────────────────────────

$matched = false;

foreach ($routes as [$routeMethod, $pattern, $handler]) {
    // Skip wrong method
    if ($routeMethod !== $method && $method !== 'HEAD') {
        // Allow HEAD on GET routes
        if (!($method === 'HEAD' && $routeMethod === 'GET')) continue;
    }

    // Convert pattern to regex
    $regex = preg_replace_callback('/:([a-zA-Z_]+)/', function ($m) {
        return '(?P<' . $m[1] . '>[^/]+)';
    }, $pattern);
    $regex = '#^' . $regex . '$#';

    if (preg_match($regex, $path, $matches)) {
        $matched = true;

        // Extract named params
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        // Dispatch to controller
        [$controllerClass, $action] = explode('@', $handler);

        if (!class_exists($controllerClass)) {
            // Return 404 if controller doesn't exist yet
            http_response_code(404);
            include ROOT_PATH . '/app/Views/errors/404.php';
            exit;
        }

        $controller = new $controllerClass();
        if (!method_exists($controller, $action)) {
            http_response_code(404);
            include ROOT_PATH . '/app/Views/errors/404.php';
            exit;
        }

        // Call the action with URL params
        call_user_func_array([$controller, $action], $params);
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    $errorView = ROOT_PATH . '/app/Views/errors/404.php';
    if (file_exists($errorView)) {
        include $errorView;
    } else {
        echo '<h1>404 - Page Not Found</h1>';
    }
}
