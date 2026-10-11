<?php
/**
 * Property API Controller
 * REST endpoints for mobile app / AJAX consumption.
 * All responses are JSON.
 */

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\PropertyModel;
use App\Helpers\{Security, Request, Paginator, Database};
use App\Services\AuditService;

class PropertyApiController extends BaseController
{
    // ── GET /api/properties ───────────────────────────────────

    public function index(): void
    {
        $filters = $this->extractFilters();
        $perPage = min(50, max(1, (int) Request::get('per_page', 12)));
        $page    = max(1, (int) Request::get('page', 1));
        $offset  = ($page - 1) * $perPage;

        $total      = PropertyModel::searchCount($filters);
        $properties = PropertyModel::search($filters, $perPage, $offset);

        $this->json([
            'success' => true,
            'data'    => $properties,
            'meta'    => [
                'total'       => $total,
                'per_page'    => $perPage,
                'current_page'=> $page,
                'last_page'   => (int) ceil($total / $perPage),
                'from'        => $offset + 1,
                'to'          => min($offset + $perPage, $total),
            ],
        ]);
    }

    // ── GET /api/properties/:id ───────────────────────────────

    public function show(string $id): void
    {
        $property = PropertyModel::findById((int) $id, true);

        if (!$property || $property['status'] !== 'verified') {
            $this->json(['success' => false, 'message' => 'Property not found.'], 404);
        }

        // Increment view count
        $viewKey = 'api_viewed_' . $id;
        if (empty($_SESSION[$viewKey])) {
            PropertyModel::incrementViewCount((int) $id);
            $_SESSION[$viewKey] = true;
        }

        $this->json(['success' => true, 'data' => $property]);
    }

    // ── GET /api/search ───────────────────────────────────────

    public function search(): void
    {
        $this->index(); // same logic, different route alias
    }

    // ── POST /api/properties/:id/favorite ────────────────────

    public function toggleFavorite(string $id): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        // Verify CSRF for non-GET requests
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? Request::post('_csrf_token', '');
        if (!hash_equals($_SESSION['_csrf_token'] ?? '', $token)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
        }

        $property = PropertyModel::findById((int) $id);
        if (!$property) {
            $this->json(['success' => false, 'message' => 'Property not found.'], 404);
        }

        $saved = PropertyModel::toggleSave((int) $id, (int) $_SESSION['user_id']);

        $this->json([
            'success' => true,
            'saved'   => $saved,
            'message' => $saved ? 'Property saved.' : 'Property removed from saved.',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────

    private function extractFilters(): array
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
            'electricity'   => Request::get('electricity', ''),
            'walk_max'      => Security::sanitizeInt(Request::get('walk_max', 0)) ?: null,
            'vehicle_access'=> Request::get('vehicle_access', ''),
            'ceiling_type'  => Security::sanitizeInt(Request::get('ceiling_type', 0)) ?: null,
            'flooring_type' => Security::sanitizeInt(Request::get('flooring_type', 0)) ?: null,
            'featured'      => Request::get('featured', ''),
            'status'        => 'verified',
        ];
    }
}
