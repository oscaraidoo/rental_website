<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PropertyModel;
use App\Models\PropertyConditionModel;
use App\Helpers\{Security, Request, Paginator, Database, Upload, Str};
use App\Services\{AuditService, NotificationService, EmailService};

class PropertyController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();

        $filters = [
            'status' => Request::get('status', ''),
            'type_id'=> Request::get('type_id', ''),
            'search' => Security::sanitize(Request::get('q', '')),
        ];

        $total      = PropertyModel::count($filters);
        $pager      = Paginator::fromRequest($total, 25);
        $properties = PropertyModel::all($pager->perPage, $pager->offset, $filters);
        $types      = Database::fetchAll("SELECT id, name FROM property_types WHERE active=1 ORDER BY sort_order");

        $this->adminView('admin.properties.index', compact('properties', 'filters', 'pager', 'total', 'types') + [
            'title' => 'Manage Properties', 'breadcrumb' => ['Properties'],
        ]);
    }

    public function show(string $id): void
    {
        $this->adminOnly();
        $property = PropertyModel::findById((int) $id, true);
        if (!$property) $this->notFound();

        $statusHistory = PropertyModel::getStatusHistory((int) $id);
        $conditions    = PropertyConditionModel::forProperty((int) $id);

        $this->adminView('admin.properties.show', compact('property', 'statusHistory', 'conditions') + [
            'title' => 'Property: ' . $property['title'],
            'breadcrumb' => ['Properties', $property['reference']],
        ]);
    }

    public function approve(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $property = PropertyModel::findById((int) $id);
        if (!$property) $this->notFound();

        PropertyModel::updateStatus((int) $id, 'verified', $this->currentUserId());

        // Notify landlord
        $landlordUserId = $this->getLandlordUserId($property['landlord_id']);
        if ($landlordUserId) {
            NotificationService::propertyApproved($landlordUserId, $property['title'], (int) $id, (string) ($property['slug'] ?? ''));
            // Email
            $user = \App\Models\UserModel::findById($landlordUserId);
            if ($user) {
                $cfg = require ROOT_PATH . '/config/app.php';
                EmailService::sendPropertyApproved(
                    $user['email'],
                    $user['first_name'],
                    $property['title'],
                    $cfg['url'] . '/properties/' . $property['slug']
                );
            }
        }

        AuditService::log('approve_property', 'properties', (int) $id, 'property', ['status' => $property['status']], ['status' => 'verified']);
        $this->success("Property \"{$property['title']}\" approved and published.");
        $this->redirect('admin/properties');
    }

    public function reject(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $property = PropertyModel::findById((int) $id);
        if (!$property) $this->notFound();

        $reason = Security::sanitize(Request::post('reason', ''));
        PropertyModel::updateStatus((int) $id, 'rejected', $this->currentUserId(), $reason);

        $landlordUserId = $this->getLandlordUserId($property['landlord_id']);
        if ($landlordUserId) {
            NotificationService::propertyRejected($landlordUserId, $property['title'], $reason, (int) $id);
            $user = \App\Models\UserModel::findById($landlordUserId);
            if ($user) {
                EmailService::sendPropertyRejected($user['email'], $user['first_name'], $property['title'], $reason);
            }
        }

        AuditService::log('reject_property', 'properties', (int) $id, 'property', [], ['reason' => $reason]);
        $this->success("Property rejected.");
        $this->redirect('admin/properties');
    }

    public function verify(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $property = PropertyModel::findById((int) $id);
        if (!$property) $this->notFound();

        // Generate verification reference
        $verRef = 'VER-' . strtoupper(bin2hex(random_bytes(4)));

        Database::insert('property_verifications', [
            'property_id'       => (int) $id,
            'verified_by'       => $this->currentUserId(),
            'status'            => 'passed',
            'verification_ref'  => $verRef,
            'overall_score'     => 100,
            'dimensions_confirmed'   => 1,
            'images_confirmed'       => 1,
            'facilities_confirmed'   => 1,
            'accessibility_confirmed'=> 1,
            'location_confirmed'     => 1,
            'rent_confirmed'         => 1,
            'landlord_id_confirmed'  => 1,
            'utilities_confirmed'    => 1,
            'availability_confirmed' => 1,
            'completed_at'           => date('Y-m-d H:i:s'),
            'notes'                  => Security::sanitize(Request::post('notes', '')),
        ]);

        PropertyModel::update((int) $id, [
            'verified'         => 1,
            'verified_at'      => date('Y-m-d H:i:s'),
            'verified_by'      => $this->currentUserId(),
            'verification_ref' => $verRef,
        ]);

        AuditService::log('verify_property', 'properties', (int) $id, 'property');
        $this->success("Property verified. Reference: {$verRef}");
        $this->redirect('admin/properties/' . $id);
    }

    public function feature(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $days  = max(1, min(90, (int) Request::post('days', 30)));
        $until = date('Y-m-d', strtotime("+{$days} days"));

        PropertyModel::update((int) $id, ['is_featured' => 1, 'featured_until' => $until]);

        // Notify landlord their property is featured
        $landlordUserId = $this->getLandlordUserId($property['landlord_id']);
        if ($landlordUserId) {
            NotificationService::propertyFeatured($landlordUserId, $property['title'], (int) $id, $until);
        }

        AuditService::log('feature_property', 'properties', (int) $id, 'property');
        $this->success("Property featured until {$until}.");
        $this->redirect('admin/properties/' . $id);
    }

    public function suspend(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $reason = Security::sanitize(Request::post('reason', ''));
        PropertyModel::updateStatus((int) $id, 'suspended', $this->currentUserId(), $reason);

        // Notify landlord with the reason
        $landlordUserId = $this->getLandlordUserId($property['landlord_id']);
        if ($landlordUserId) {
            NotificationService::propertySuspended($landlordUserId, $property['title'], $reason, (int) $id);
        }

        AuditService::log('suspend_property', 'properties', (int) $id, 'property');
        $this->success('Property suspended.');
        $this->redirect('admin/properties');
    }

    public function delete(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        PropertyModel::softDelete((int) $id);
        AuditService::log('delete_property', 'properties', (int) $id, 'property');
        $this->success('Property deleted.');
        $this->redirect('admin/properties');
    }

    // ══════════════════════════════════════════════════════════
    // Full property management — the landlord module was removed,
    // so the admin owns property creation and editing end-to-end.
    // ══════════════════════════════════════════════════════════

    public function create(): void
    {
        $this->adminOnly();
        $this->renderForm(null);
    }

    public function store(): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $data   = $this->collectPropertyData();
        $errors = $this->validateProperty($data);
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = $data;
            $this->redirect('admin/properties/create');
        }

        try {
            Database::beginTransaction();

            $landlordId = $this->resolveLandlordId((int) $data['owner_user_id']);
            unset($data['owner_user_id'], $data['status']);

            $propertyId = PropertyModel::create($landlordId, $data);
            $this->saveSubResources($propertyId);
            $added = $this->handleImageUploads($propertyId, $this->currentUserId());

            // Status set through updateStatus so the history trail is written.
            PropertyModel::updateStatus(
                $propertyId,
                Request::post('status', 'verified'),
                $this->currentUserId(),
                'Created by admin'
            );

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            error_log('Admin property create failed: ' . $e->getMessage());
            $this->error('Could not create the property. Please try again.');
            $this->redirect('admin/properties/create');
        }

        unset($_SESSION['form_old']);
        AuditService::log('create_property', 'properties', $propertyId, 'property', [],
            ['title' => $data['title'], 'images_added' => $added],
            "Admin created property {$data['title']}");
        $this->success("Property \"{$data['title']}\" created.");
        $this->redirect('admin/properties/' . $propertyId);
    }

    public function edit(string $id): void
    {
        $this->adminOnly();
        $property = PropertyModel::findById((int) $id, true);
        if (!$property) $this->notFound();

        $this->renderForm($property);
    }

    public function update(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $propertyId = (int) $id;
        $property   = PropertyModel::findById($propertyId);
        if (!$property) $this->notFound();

        $data   = $this->collectPropertyData();
        $errors = $this->validateProperty($data);
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = $data;
            $this->redirect('admin/properties/' . $propertyId . '/edit');
        }

        try {
            Database::beginTransaction();

            // Re-assign owner if changed (keeps the landlords FK intact)
            $landlordId = $this->resolveLandlordId((int) $data['owner_user_id']);
            unset($data['owner_user_id'], $data['status']);
            $data['landlord_id'] = $landlordId;

            PropertyModel::update($propertyId, $data); // auto-regenerates slug on title change
            $this->saveSubResources($propertyId);
            $added = $this->handleImageUploads($propertyId, $this->currentUserId());

            if (Request::post('status', $property['status']) !== $property['status']) {
                PropertyModel::updateStatus($propertyId, Request::post('status'), $this->currentUserId(), 'Updated by admin');
            }

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            error_log('Admin property update failed: ' . $e->getMessage());
            $this->error('Could not update the property. Please try again.');
            $this->redirect('admin/properties/' . $propertyId . '/edit');
        }

        unset($_SESSION['form_old']);
        AuditService::log('update_property', 'properties', $propertyId, 'property',
            ['status' => $property['status'], 'title' => $property['title']],
            ['title' => $data['title'], 'images_added' => $added]);
        $this->success('Property updated.' . ($added ? " {$added} image(s) added." : ''));
        $this->redirect('admin/properties/' . $propertyId);
    }

    public function addImage(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $propertyId = (int) $id;
        if (!PropertyModel::findById($propertyId)) $this->notFound();

        $added = $this->handleImageUploads($propertyId, $this->currentUserId());
        if ($added > 0) {
            AuditService::log('upload_property_images', 'properties', $propertyId, 'property', [], ['count' => $added]);
            $this->success("{$added} image(s) uploaded.");
        } else {
            $this->error('No valid image was uploaded. JPEG, PNG, WebP or GIF up to 5 MB.');
        }
        $this->redirect('admin/properties/' . $propertyId);
    }

    /**
     * Import an image by URL (e.g. from a listing site or Google) — downloads,
     * validates and stores it locally so the property has its own copy.
     */
    public function importImage(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $propertyId = (int) $id;
        if (!PropertyModel::findById($propertyId)) $this->notFound();

        $url = trim((string) (Request::post('image_url', '')));
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('Please paste a valid image URL (https://…).');
            $this->redirect('admin/properties/' . $propertyId);
        }

        // Optional caption/category override, same whitelist as uploads
        $category = Security::sanitize(Request::post('image_category', 'other'));
        if (!in_array($category, ['exterior', 'living_room', 'bedroom', 'kitchen', 'bathroom',
                                  'toilet', 'porch', 'compound', 'parking', 'road', 'other'], true)) {
            $category = 'other';
        }
        $caption = Security::sanitize(Request::post('caption', '')) ?: null;

        $res = Upload::fetchFromUrl($url);
        if (!$res['success']) {
            $this->error('Import failed: ' . ($res['error'] ?? 'unknown error'));
            $this->redirect('admin/properties/' . $propertyId);
        }

        $width = $height = null;
        $full = ROOT_PATH . '/public/' . $res['path'];
        if (file_exists($full)) {
            $dims = @getimagesize($full);
            if ($dims) { $width = (int) $dims[0]; $height = (int) $dims[1]; }
        }

        $hasPrimary = (bool) Database::fetchColumn(
            "SELECT id FROM property_images WHERE property_id = ? AND is_primary = 1", [$propertyId]
        );
        $sort = (int) Database::fetchColumn(
            "SELECT COALESCE(MAX(sort_order), -1) FROM property_images WHERE property_id = ?", [$propertyId]
        ) + 1;

        PropertyModel::addImage($propertyId, [
            'filename'   => $res['filename'],
            'path'       => $res['path'],
            'thumbnail'  => $res['thumbnail'],
            'category'   => $category,
            'caption'    => $caption ?: 'Imported from web',
            'sort_order' => $sort,
            'is_primary' => $hasPrimary ? 0 : 1,
            'file_size'  => (int) $res['size'],
            'width'      => $width,
            'height'     => $height,
            'uploaded_by'=> $this->currentUserId(),
        ]);

        AuditService::log('import_property_image', 'properties', $propertyId, 'property',
            [], ['source' => $url], 'Admin imported an image from a URL');
        $this->success('Image imported from the web and added to this property.');
        $this->redirect('admin/properties/' . $propertyId . '#images');
    }

    public function setPrimaryImage(string $imageId): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $img = Database::fetchOne("SELECT id, property_id FROM property_images WHERE id = ?", [(int) $imageId]);
        if (!$img) $this->notFound();

        PropertyModel::setImagePrimary((int) $img['property_id'], (int) $img['id']);
        AuditService::log('set_primary_image', 'properties', (int) $img['property_id'], 'property');
        $this->success('Primary image updated.');
        $this->redirect('admin/properties/' . $img['property_id']);
    }

    public function deleteImage(string $imageId): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $img = Database::fetchOne("SELECT id, property_id FROM property_images WHERE id = ?", [(int) $imageId]);
        if (!$img) $this->notFound();

        PropertyModel::deleteImage((int) $img['property_id'], (int) $img['id']);
        AuditService::log('delete_property_image', 'properties', (int) $img['property_id'], 'property');
        $this->success('Image deleted.');
        $this->redirect('admin/properties/' . $img['property_id']);
    }

    private function getLandlordUserId(int $landlordId): ?int
    {
        $uid = Database::fetchColumn("SELECT user_id FROM landlords WHERE id = ?", [$landlordId]);
        return $uid ? (int) $uid : null;
    }

    // ── Landlord contact details (admin posts, renters pay to view) ──

    public function saveLandlordContact(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $property = PropertyModel::findById((int) $id);
        if (!$property) $this->notFound();

        $phone   = Security::sanitize(Request::post('contact_phone', ''));
        $phone2  = Security::sanitize(Request::post('contact_phone2', ''));
        $email   = Security::sanitizeEmail(Request::post('contact_email', ''));
        $address = Security::sanitize(Request::post('contact_address', ''));
        $notes   = Security::sanitize(Request::post('contact_notes', ''));

        Database::query(
            "UPDATE landlords SET
                contact_phone   = ?,
                contact_phone2  = ?,
                contact_email   = ?,
                contact_address = ?,
                contact_notes   = ?
             WHERE id = ?",
            [
                $phone   ?: null,
                $phone2  ?: null,
                $email   ?: null,
                $address ?: null,
                $notes   ?: null,
                (int) $property['landlord_id'],
            ]
        );

        AuditService::log('update_landlord_contact', 'properties', (int) $id, 'property',
            [], ['phone' => $phone, 'email' => $email],
            "Admin updated landlord contact for property #{$id}"
        );

        $this->success('Landlord contact details saved.');
        $this->redirect('admin/properties/' . $id);
    }

    private function renderForm(?array $property): void
    {
        $options = [
            'types'         => Database::fetchAll("SELECT id, name FROM property_types WHERE active = 1 ORDER BY sort_order, name"),
            'regions'       => Database::fetchAll("SELECT id, name FROM regions WHERE active = 1 ORDER BY name"),
            'amenities'     => Database::fetchAll("SELECT id, name, category FROM amenities WHERE active = 1 ORDER BY category, sort_order, name"),
            'ceilingTypes'  => Database::fetchAll("SELECT id, name FROM ceiling_types ORDER BY name"),
            'flooringTypes' => Database::fetchAll("SELECT id, name FROM flooring_types ORDER BY name"),
            'owners'        => Database::fetchAll("SELECT id, CONCAT(first_name,' ',last_name) AS name, email FROM users WHERE deleted_at IS NULL AND status = 'active' ORDER BY first_name, last_name"),
        ];

        if ($property) {
            $propertyId = (int) $property['id'];
            $p = $property;
            $p['owner_user_id'] = (int) Database::fetchColumn(
                "SELECT user_id FROM landlords WHERE id = ?", [$property['landlord_id']]
            );
            $amenityIds    = array_map('intval', array_column($property['amenities'] ?? [], 'id'));
            $location      = $property['location'] ?? null;
            $spaces        = $property['spaces'] ?? [];
            $accessibility = $property['accessibility'] ?? null;
            $security      = $property['security'] ?? null;
            $images        = $property['images'] ?? [];

            $this->adminView('admin.properties.edit',
                compact('p', 'options', 'propertyId', 'amenityIds', 'location', 'spaces', 'accessibility', 'security', 'images')
                + ['title' => 'Edit: ' . $property['title'], 'breadcrumb' => ['Properties', 'Edit']]);
            return;
        }

        $old = $_SESSION['form_old'] ?? [];
        unset($_SESSION['form_old']);

        $p = [
            'owner_user_id'     => $this->currentUserId() ?? 0,
            'title'             => '', 'description' => '', 'property_type_id' => 0,
            'rent_monthly'      => '', 'security_deposit' => '', 'negotiable' => 0,
            'available_from'    => '', 'num_bedrooms' => 1, 'num_bathrooms' => 1,
            'num_toilets'       => 1, 'num_kitchens' => 1, 'furnished' => 'unfurnished',
            'is_self_contained' => 0, 'building_level' => 'ground',
            'ceiling_type_id'   => 0, 'flooring_type_id' => 0,
            'water_available'   => 0, 'electricity_type' => '', 'has_parking' => 0,
            'pet_policy'        => 'not_allowed', 'status' => 'verified',
        ];
        foreach ($old as $k => $v) {
            if (array_key_exists($k, $p)) $p[$k] = $v;
        }

        $propertyId = null; $amenityIds = [];
        $location = null; $spaces = []; $accessibility = null; $security = null; $images = [];

        $this->adminView('admin.properties.create',
            compact('p', 'options', 'propertyId', 'amenityIds', 'location', 'spaces', 'accessibility', 'security', 'images')
            + ['title' => 'Add Property', 'breadcrumb' => ['Properties', 'Add']]);
    }

    private function collectPropertyData(): array
    {
        $in = function (string $key, array $allowed, string $default): string {
            $v = Security::sanitize(Request::post($key, $default));
            return in_array($v, $allowed, true) ? $v : $default;
        };

        return [
            'owner_user_id'     => (int) Request::post('owner_user_id', 0),
            'title'             => Security::sanitize(Request::post('title', '')),
            'description'       => Security::sanitize(Request::post('description', '')),
            'property_type_id'  => (int) Request::post('property_type_id', 0),
            'rent_monthly'      => (float) Request::post('rent_monthly', 0),
            'security_deposit'  => (Request::post('security_deposit') !== '' && Request::post('security_deposit') !== null)
                                    ? (float) Request::post('security_deposit') : null,
            'negotiable'        => Request::post('negotiable') ? 1 : 0,
            'available_from'    => Security::sanitize(Request::post('available_from', '')) ?: null,
            'num_bedrooms'      => max(0, (int) Request::post('num_bedrooms', 0)),
            'num_bathrooms'     => max(0, (int) Request::post('num_bathrooms', 0)),
            'num_toilets'       => max(0, (int) Request::post('num_toilets', 0)),
            'num_kitchens'      => max(0, (int) Request::post('num_kitchens', 0)),
            'furnished'         => $in('furnished', ['furnished', 'semi_furnished', 'unfurnished'], 'unfurnished'),
            'is_self_contained' => Request::post('is_self_contained') ? 1 : 0,
            'building_level'    => $in('building_level', ['ground', 'first', 'second', 'third', 'higher'], 'ground'),
            'ceiling_type_id'   => (int) Request::post('ceiling_type_id', 0) ?: null,
            'flooring_type_id'  => (int) Request::post('flooring_type_id', 0) ?: null,
            'water_available'   => Request::post('water_available') ? 1 : 0,
            'electricity_type'  => Str::arrayToSet(array_intersect(
                                    (array) Request::post('electricity_type', []),
                                    ['self_meter', 'shared_meter', 'prepaid', 'postpaid', 'ecg', 'other'])) ?: null,
            'has_parking'       => Request::post('has_parking') ? 1 : 0,
            'pet_policy'        => $in('pet_policy', ['allowed', 'not_allowed', 'certain_allowed', 'approval_required'], 'not_allowed'),
            'status'            => $in('status', ['draft', 'submitted', 'under_review', 'inspection_required',
                                      'verified', 'rejected', 'suspended', 'rented', 'expired'], 'verified'),
        ];
    }

    private function validateProperty(array $d): array
    {
        $errors = [];
        if ($d['title'] === '') {
            $errors[] = 'Title is required.';
        } elseif (mb_strlen($d['title']) > 200) {
            $errors[] = 'Title must be 200 characters or fewer.';
        }
        if ($d['property_type_id'] <= 0 ||
            !Database::fetchColumn("SELECT id FROM property_types WHERE id = ?", [$d['property_type_id']])) {
            $errors[] = 'Please choose a valid property type.';
        }
        if ((float) $d['rent_monthly'] <= 0) {
            $errors[] = 'Monthly rent must be greater than zero.';
        }
        if ($d['owner_user_id'] <= 0 ||
            !Database::fetchColumn("SELECT id FROM users WHERE id = ? AND deleted_at IS NULL", [$d['owner_user_id']])) {
            $errors[] = 'Please choose a valid owner.';
        }
        if (mb_strlen($d['description']) > 5000) {
            $errors[] = 'Description must be 5000 characters or fewer.';
        }
        return $errors;
    }

    private function collectLocationData(): array
    {
        $regionId = (int) Request::post('region_id', 0) ?: null;
        $cityId   = (int) Request::post('city_id', 0) ?: null;
        $areaId   = (int) Request::post('area_id', 0) ?: null;

        $community       = Security::sanitize(Request::post('community', ''))       ?: null;
        $street          = Security::sanitize(Request::post('street', ''))          ?: null;
        $digitalAddress  = Security::sanitize(Request::post('digital_address', '')) ?: null;
        $landmark        = Security::sanitize(Request::post('landmark', ''))        ?: null;

        if (!$regionId && !$cityId && !$areaId && !$community && !$street && !$digitalAddress && !$landmark) {
            return []; // nothing supplied
        }

        // Auto-fill municipality from the chosen area
        $municipality = $areaId
            ? Database::fetchColumn("SELECT municipality FROM areas WHERE id = ?", [$areaId])
            : null;

        return [
            'region_id'       => $regionId,
            'city_id'         => $cityId,
            'area_id'         => $areaId,
            'municipality'    => $municipality ?: null,
            'community'       => $community,
            'street'          => $street,
            'digital_address' => $digitalAddress,
            'landmark'        => $landmark,
        ];
    }

    private function saveSubResources(int $propertyId): void
    {
        $loc = $this->collectLocationData();
        if (!empty($loc)) PropertyModel::saveLocation($propertyId, $loc);

        $spaces = Request::post('spaces', []);
        if (is_array($spaces)) PropertyModel::saveSpaces($propertyId, $spaces);

        $road = Security::sanitize(Request::post('road_condition', ''));
        PropertyModel::saveAccessibility($propertyId, [
            'distance_main_road_km' => Request::post('distance_main_road_km') !== '' && Request::post('distance_main_road_km') !== null
                                        ? (float) Request::post('distance_main_road_km') : null,
            'walking_time_minutes'  => Request::post('walking_time_minutes') !== '' && Request::post('walking_time_minutes') !== null
                                        ? (int) Request::post('walking_time_minutes') : null,
            'road_condition'        => in_array($road, ['tarred', 'gravel', 'dirt', 'mixed'], true) ? $road : null,
            'vehicle_accessible'    => Request::post('vehicle_accessible') ? 1 : 0,
            'car_accessible'        => Request::post('car_accessible') ? 1 : 0,
            'taxi_accessible'       => Request::post('taxi_accessible') ? 1 : 0,
            'motorbike_accessible'  => Request::post('motorbike_accessible') ? 1 : 0,
        ]);

        PropertyModel::saveSecurity($propertyId, [
            'gated'            => Request::post('gated') ? 1 : 0,
            'security_guard'   => Request::post('security_guard') ? 1 : 0,
            'cctv'             => Request::post('cctv') ? 1 : 0,
            'street_lighting'  => Request::post('street_lighting') ? 1 : 0,
            'fenced'           => Request::post('fenced') ? 1 : 0,
            'intercom'         => Request::post('intercom') ? 1 : 0,
            'security_notes'   => Security::sanitize(Request::post('security_notes', '')) ?: null,
        ]);

        $amenityIds = Request::post('amenity_ids', []);
        PropertyModel::saveAmenities($propertyId, is_array($amenityIds) ? $amenityIds : []);
    }

    /**
     * Process the multi-file 'images' input. Returns the number of images stored.
     * Invalid files are skipped with a flash message; valid ones never abort the flow.
     */
    private function handleImageUploads(int $propertyId, int $userId): int
    {
        if (empty($_FILES['images']) || !is_array($_FILES['images']['name'] ?? null)) return 0;
        $files = $_FILES['images'];

        $category = Security::sanitize(Request::post('image_category', 'other'));
        if (!in_array($category, ['exterior', 'living_room', 'bedroom', 'kitchen', 'bathroom',
                                  'toilet', 'porch', 'compound', 'parking', 'road', 'other'], true)) {
            $category = 'other';
        }

        $hasPrimary = (bool) Database::fetchColumn(
            "SELECT id FROM property_images WHERE property_id = ? AND is_primary = 1", [$propertyId]
        );
        $sort = (int) Database::fetchColumn(
            "SELECT COALESCE(MAX(sort_order), -1) FROM property_images WHERE property_id = ?", [$propertyId]
        ) + 1;

        $added = 0;
        $skipped = [];

        foreach ($files['name'] as $i => $originalName) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;

            $file = [
                'name'     => $originalName,
                'type'     => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error'    => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $files['size'][$i] ?? 0,
            ];

            $res = Upload::handle($file, 'image', 'properties/images');
            if (!$res['success']) {
                $skipped[] = basename((string) $originalName) . ': ' . ($res['error'] ?? 'upload failed');
                continue;
            }

            $width = $height = null;
            $full = ROOT_PATH . '/' . $res['path'];
            if (file_exists($full)) {
                $dims = @getimagesize($full);
                if ($dims) { $width = (int) $dims[0]; $height = (int) $dims[1]; }
            }

            PropertyModel::addImage($propertyId, [
                'filename'   => $res['filename'],
                'path'       => $res['path'],
                'thumbnail'  => $res['thumbnail'],
                'category'   => $category,
                'caption'    => Security::sanitize(pathinfo((string) $originalName, PATHINFO_FILENAME)) ?: null,
                'sort_order' => $sort++,
                'is_primary' => (!$hasPrimary && $added === 0) ? 1 : 0,
                'file_size'  => (int) $res['size'],
                'width'      => $width,
                'height'     => $height,
                'uploaded_by'=> $userId,
            ]);
            $added++;
        }

        if (!empty($skipped)) {
            $this->error('Some files were skipped: ' . implode('; ', array_slice($skipped, 0, 3)));
        }
        return $added;
    }

    /**
     * The landlords table remains the ownership anchor for properties even
     * though the landlord module is gone — make sure the chosen user has a
     * row and return its id (same legacy pattern as Admin\UserController).
     */
    private function resolveLandlordId(int $userId): int
    {
        if ($userId <= 0) $userId = $this->currentUserId() ?? 0;

        $id = Database::fetchColumn("SELECT id FROM landlords WHERE user_id = ?", [$userId]);
        if ($id) return (int) $id;

        Database::query("INSERT IGNORE INTO landlords (user_id) VALUES (?)", [$userId]);
        return (int) Database::fetchColumn("SELECT id FROM landlords WHERE user_id = ?", [$userId]);
    }

    // ── Property Condition Module ───────────────────────────

    private const CONDITION_FIELDS = [
        'overall_condition', 'structure', 'plumbing', 'electrical',
        'paint_finishing', 'fittings', 'roof', 'windows_doors', 'cleanliness',
    ];

    public function storeCondition(string $id): void
    {
        $this->saveCondition((int) $id, null);
    }

    public function updateCondition(string $id, string $conditionId): void
    {
        $this->saveCondition((int) $id, (int) $conditionId);
    }

    private function saveCondition(int $propertyId, ?int $conditionId): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $property = PropertyModel::findById($propertyId, true);
        if (!$property) $this->notFound();

        $existing = null;
        if ($conditionId !== null) {
            $existing = PropertyConditionModel::find($conditionId);
            if (!$existing || (int) $existing['property_id'] !== $propertyId) $this->notFound();
        }

        // ── Collect aspect ratings (must be valid enum values) ──
        $data = [];
        foreach (self::CONDITION_FIELDS as $field) {
            $val = Security::sanitize(Request::post($field, ''));
            if ($val === '') {
                $data[$field] = $field === 'overall_condition' ? 'good' : null;
            } elseif (in_array($val, PropertyConditionModel::LEVELS, true)) {
                $data[$field] = $val;
            } else {
                $data[$field] = $field === 'overall_condition' ? 'good' : null;
            }
        }

        $data['summary']         = Security::sanitize(Request::post('summary', ''));
        $data['issues']          = Security::sanitize(Request::post('issues', ''));
        $data['internal_notes']  = Security::sanitize(Request::post('internal_notes', ''));
        $data['assessment_date'] = Request::post('assessment_date', '') ?: date('Y-m-d');
        $data['active']          = (int) !empty(Request::post('active'));

        // ── Photo uploads (append to existing on update) ──
        $photos = $existing ? PropertyConditionModel::photos($existing['photos'] ?? null) : [];
        foreach ($_FILES['photos']['name'] ?? [] as $i => $name) {
            if (($_FILES['photos']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
            $file = [
                'name'     => $_FILES['photos']['name'][$i],
                'type'     => $_FILES['photos']['type'][$i],
                'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                'error'    => $_FILES['photos']['error'][$i],
                'size'     => $_FILES['photos']['size'][$i],
            ];
            $up = Upload::handle($file, 'image', 'conditions');
            if (!empty($up['success'])) {
                $photos[] = ['path' => $up['path'], 'caption' => ''];
            } else {
                $this->error('Photo upload failed: ' . ($up['error'] ?? 'unknown error'));
                $this->redirect('admin/properties/' . $propertyId . '#condition');
                return;
            }
        }
        $data['photos'] = $photos ? json_encode(array_values($photos), JSON_UNESCAPED_SLASHES) : null;

        if ($existing) {
            $data['assessed_by'] = $this->currentUserId();
            PropertyConditionModel::update((int) $existing['id'], $data);
            AuditService::log('update_property_condition', 'properties', (int) $existing['id'], 'property_condition', $existing, $data);
            $this->success('Condition report updated.');
            $conditionId = (int) $existing['id'];
        } else {
            $data['property_id'] = $propertyId;
            $data['assessed_by'] = $this->currentUserId();
            $conditionId = (int) PropertyConditionModel::create($data);
            AuditService::log('create_property_condition', 'properties', $conditionId, 'property_condition', [], $data);
            $this->success('Condition report added.');
        }

        // Keep a single active report per property
        if ($data['active']) {
            PropertyConditionModel::makeOnlyActive($propertyId, $conditionId);
        }

        $this->redirect('admin/properties/' . $propertyId . '#condition');
    }

    public function deleteCondition(string $id, string $conditionId): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $condition = PropertyConditionModel::find((int) $conditionId);
        if (!$condition || (int) $condition['property_id'] !== (int) $id) $this->notFound();

        foreach (PropertyConditionModel::photos($condition['photos'] ?? null) as $photo) {
            if (!empty($photo['path'])) Upload::delete($photo['path']);
        }
        PropertyConditionModel::delete((int) $condition['id']);
        AuditService::log('delete_property_condition', 'properties', (int) $condition['id'], 'property_condition', $condition, []);
        $this->success('Condition report deleted.');
        $this->redirect('admin/properties/' . $id . '#condition');
    }

    public function deleteConditionPhoto(string $id, string $conditionId, string $index): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $condition = PropertyConditionModel::find((int) $conditionId);
        if (!$condition || (int) $condition['property_id'] !== (int) $id) $this->notFound();

        $photos = PropertyConditionModel::photos($condition['photos'] ?? null);
        $idx = (int) $index;
        if (!isset($photos[$idx])) $this->notFound();

        if (!empty($photos[$idx]['path'])) Upload::delete($photos[$idx]['path']);
        unset($photos[$idx]);
        PropertyConditionModel::update((int) $condition['id'], [
            'photos' => $photos ? json_encode(array_values($photos), JSON_UNESCAPED_SLASHES) : null,
        ]);
        $this->success('Photo removed.');
        $this->redirect('admin/properties/' . $id . '#condition');
    }
}
