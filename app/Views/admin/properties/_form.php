<?php
use App\Helpers\{Url, Security, Str};

/** @var array $p current values (property row or old input) */
/** @var array $options types/regions/amenities/ceilingTypes/flooringTypes/owners */
/** @var int|null $propertyId null = create */
/** @var array $amenityIds */
/** @var array|null $location */
/** @var array $spaces */
/** @var array|null $accessibility */
/** @var array|null $security */
/** @var array $images */

$isEdit   = $propertyId !== null;
$electric = Str::setToArray($p['electricity_type'] ?? '');
$statuses = ['draft','submitted','under_review','inspection_required','verified','rejected','suspended','rented','expired'];
$spaceTypes = ['bedroom','living_room','kitchen','bathroom','toilet','dining_room','porch','store','garage','other'];
$electricOptions = ['self_meter' => 'Self meter', 'shared_meter' => 'Shared meter', 'prepaid' => 'Prepaid',
                    'postpaid' => 'Postpaid', 'ecg' => 'ECG', 'other' => 'Other'];

$action = $isEdit
    ? Url::base('admin/properties/' . $propertyId . '/update')
    : Url::base('admin/properties/create');
?>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data">
    <?= Security::csrfField() ?>

    <!-- ── Basics ─────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold border-0 py-3">Basics</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required maxlength="200"
                           placeholder="e.g. 2-Bedroom Self-Contained at Effia"
                           value="<?= Security::e((string)($p['title'] ?? '')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Description</label>
                    <textarea name="description" class="form-control" rows="5" maxlength="5000"
                              placeholder="Describe the property, the neighbourhood, utilities, rules..."><?= Security::e((string)($p['description'] ?? '')) ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Property type <span class="text-danger">*</span></label>
                    <select name="property_type_id" class="form-select" required>
                        <option value="">Choose type…</option>
                        <?php foreach ($options['types'] as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" <?= (int)($p['property_type_id'] ?? 0) === (int)$t['id'] ? 'selected' : '' ?>>
                            <?= Security::e($t['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Owner <span class="text-danger">*</span></label>
                    <select name="owner_user_id" class="form-select" required>
                        <option value="">Choose owner…</option>
                        <?php foreach ($options['owners'] as $o): ?>
                        <option value="<?= (int)$o['id'] ?>" <?= (int)($p['owner_user_id'] ?? 0) === (int)$o['id'] ? 'selected' : '' ?>>
                            <?= Security::e($o['name']) ?> (<?= Security::e($o['email']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">The account this listing belongs to.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= ($p['status'] ?? 'verified') === $s ? 'selected' : '' ?>>
                            <?= ucwords(str_replace('_', ' ', $s)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">"Verified" publishes it on the public site.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Rent / month (GH₵) <span class="text-danger">*</span></label>
                    <input type="number" name="rent_monthly" class="form-control" required min="1" step="0.01"
                           value="<?= Security::e((string)($p['rent_monthly'] ?? '')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Security deposit (GH₵)</label>
                    <input type="number" name="security_deposit" class="form-control" min="0" step="0.01"
                           value="<?= Security::e((string)($p['security_deposit'] ?? '')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Available from</label>
                    <input type="date" name="available_from" class="form-control"
                           value="<?= Security::e((string)($p['available_from'] ?? '')) ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="negotiable" name="negotiable" value="1"
                               <?= !empty($p['negotiable']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="negotiable">Rent negotiable</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Rooms ──────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold border-0 py-3">Rooms &amp; Finishings</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-2">
                    <label class="form-label fw-medium">Bedrooms</label>
                    <input type="number" name="num_bedrooms" class="form-control" min="0"
                           value="<?= (int)($p['num_bedrooms'] ?? 0) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-medium">Bathrooms</label>
                    <input type="number" name="num_bathrooms" class="form-control" min="0"
                           value="<?= (int)($p['num_bathrooms'] ?? 0) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-medium">Toilets</label>
                    <input type="number" name="num_toilets" class="form-control" min="0"
                           value="<?= (int)($p['num_toilets'] ?? 0) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-medium">Kitchens</label>
                    <input type="number" name="num_kitchens" class="form-control" min="0"
                           value="<?= (int)($p['num_kitchens'] ?? 0) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-medium">Furnished</label>
                    <select name="furnished" class="form-select">
                        <?php foreach (['unfurnished' => 'Unfurnished', 'semi_furnished' => 'Semi-furnished', 'furnished' => 'Furnished'] as $v => $lbl): ?>
                        <option value="<?= $v ?>" <?= ($p['furnished'] ?? 'unfurnished') === $v ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-medium">Floor</label>
                    <select name="building_level" class="form-select">
                        <?php foreach (['ground' => 'Ground', 'first' => 'First', 'second' => 'Second', 'third' => 'Third', 'higher' => 'Higher'] as $v => $lbl): ?>
                        <option value="<?= $v ?>" <?= ($p['building_level'] ?? 'ground') === $v ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-center">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="selfContained" name="is_self_contained" value="1"
                               <?= !empty($p['is_self_contained']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="selfContained">Self-contained</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Ceiling type</label>
                    <select name="ceiling_type_id" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($options['ceilingTypes'] as $ct): ?>
                        <option value="<?= (int)$ct['id'] ?>" <?= (int)($p['ceiling_type_id'] ?? 0) === (int)$ct['id'] ? 'selected' : '' ?>>
                            <?= Security::e($ct['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Flooring type</label>
                    <select name="flooring_type_id" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($options['flooringTypes'] as $ft): ?>
                        <option value="<?= (int)$ft['id'] ?>" <?= (int)($p['flooring_type_id'] ?? 0) === (int)$ft['id'] ? 'selected' : '' ?>>
                            <?= Security::e($ft['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Utilities & rules ──────────────────────────────── -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold border-0 py-3">Utilities &amp; Rules</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="waterAvail" name="water_available" value="1"
                               <?= !empty($p['water_available']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="waterAvail">Water available</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="hasParking" name="has_parking" value="1"
                               <?= !empty($p['has_parking']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="hasParking">Parking available</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium d-block">Electricity</label>
                    <?php foreach ($electricOptions as $val => $lbl): ?>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" class="form-check-input" id="elec_<?= $val ?>" name="electricity_type[]" value="<?= $val ?>"
                               <?= in_array($val, $electric, true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="elec_<?= $val ?>" style="font-size:.85rem"><?= $lbl ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Pet policy</label>
                    <select name="pet_policy" class="form-select">
                        <?php foreach (['not_allowed' => 'Not allowed', 'allowed' => 'Allowed',
                                        'certain_allowed' => 'Certain pets allowed', 'approval_required' => 'Approval required'] as $v => $lbl): ?>
                        <option value="<?= $v ?>" <?= ($p['pet_policy'] ?? 'not_allowed') === $v ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Location ───────────────────────────────────────── -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold border-0 py-3">Location</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Region</label>
                    <select name="region_id" id="locRegion" class="form-select" data-selected="<?= (int)($location['region_id'] ?? 0) ?>">
                        <option value="">—</option>
                        <?php foreach ($options['regions'] as $r): ?>
                        <option value="<?= (int)$r['id'] ?>" <?= (int)($location['region_id'] ?? 0) === (int)$r['id'] ? 'selected' : '' ?>>
                            <?= Security::e($r['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">City / Town</label>
                    <select name="city_id" id="locCity" class="form-select" data-selected="<?= (int)($location['city_id'] ?? 0) ?>">
                        <option value="">—</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Area</label>
                    <select name="area_id" id="locArea" class="form-select" data-selected="<?= (int)($location['area_id'] ?? 0) ?>">
                        <option value="">—</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Community</label>
                    <input type="text" name="community" class="form-control"
                           value="<?= Security::e((string)($location['community'] ?? '')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Street</label>
                    <input type="text" name="street" class="form-control"
                           value="<?= Security::e((string)($location['street'] ?? '')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Digital address (GPS)</label>
                    <input type="text" name="digital_address" class="form-control" placeholder="e.g. GA-183-2074"
                           value="<?= Security::e((string)($location['digital_address'] ?? '')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Landmark</label>
                    <input type="text" name="landmark" class="form-control"
                           value="<?= Security::e((string)($location['landmark'] ?? '')) ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- ── Room dimensions ────────────────────────────────── -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold border-0 py-3 d-flex justify-content-between align-items-center">
            <span>Room Dimensions</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addSpaceRow">
                + Add room
            </button>
        </div>
        <div class="card-body" id="spaceRows">
            <?php if (empty($spaces)): ?>
            <p class="text-muted small mb-2" id="spacesEmptyNote">No rooms added yet — this is what makes listings stand out (exact sizes, walking times).</p>
            <?php endif; ?>
            <?php foreach ($spaces as $sp): ?>
            <div class="space-row">
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" name="spaces[][space_name]"
                               placeholder="Room name" value="<?= Security::e((string)($sp['space_name'] ?? '')) ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="spaces[][space_type]">
                            <?php foreach ($spaceTypes as $st): ?>
                            <option value="<?= $st ?>" <?= ($sp['space_type'] ?? 'other') === $st ? 'selected' : '' ?>>
                                <?= ucwords(str_replace('_', ' ', $st)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-4 col-md-1">
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="spaces[][length]"
                               placeholder="L" value="<?= Security::e((string)($sp['length'] ?? '')) ?>">
                    </div>
                    <div class="col-4 col-md-1">
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="spaces[][width]"
                               placeholder="W" value="<?= Security::e((string)($sp['width'] ?? '')) ?>">
                    </div>
                    <div class="col-4 col-md-1">
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="spaces[][height]"
                               placeholder="H" value="<?= Security::e((string)($sp['height'] ?? '')) ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="spaces[][unit]">
                            <?php foreach (['m' => 'meters', 'ft' => 'feet', 'cm' => 'cm'] as $v => $lbl): ?>
                            <option value="<?= $v ?>" <?= ($sp['unit'] ?? 'm') === $v ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-space">&times;</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php /* Template row cloned by JS — brackets auto-index in PHP */ ?>
            <div class="space-row d-none" id="spaceTemplate">
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" name="spaces[][space_name]" placeholder="Room name">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="spaces[][space_type]">
                            <?php foreach ($spaceTypes as $st): ?>
                            <option value="<?= $st ?>" <?= $st === 'other' ? 'selected' : '' ?>>
                                <?= ucwords(str_replace('_', ' ', $st)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-4 col-md-1">
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="spaces[][length]" placeholder="L">
                    </div>
                    <div class="col-4 col-md-1">
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="spaces[][width]" placeholder="W">
                    </div>
                    <div class="col-4 col-md-1">
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="spaces[][height]" placeholder="H">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="spaces[][unit]">
                            <?php foreach (['m' => 'meters', 'ft' => 'feet', 'cm' => 'cm'] as $v => $lbl): ?>
                            <option value="<?= $v ?>" <?= $v === 'm' ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-space">&times;</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Accessibility & security ───────────────────────── -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold border-0 py-3">Accessibility &amp; Security</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-2">
                    <label class="form-label fw-medium">Walk to road (min)</label>
                    <input type="number" min="0" class="form-control" name="walking_time_minutes"
                           value="<?= Security::e((string)($accessibility['walking_time_minutes'] ?? '')) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-medium">Distance road (km)</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="distance_main_road_km"
                           value="<?= Security::e((string)($accessibility['distance_main_road_km'] ?? '')) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-medium">Road condition</label>
                    <select name="road_condition" class="form-select">
                        <option value="">—</option>
                        <?php foreach (['tarred' => 'Tarred', 'gravel' => 'Gravel', 'dirt' => 'Dirt', 'mixed' => 'Mixed'] as $v => $lbl): ?>
                        <option value="<?= $v ?>" <?= ($accessibility['road_condition'] ?? '') === $v ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium d-block">Vehicles</label>
                    <?php foreach (['vehicle_accessible' => 'Any vehicle', 'car_accessible' => 'Car', 'taxi_accessible' => 'Taxi', 'motorbike_accessible' => 'Motorbike'] as $val => $lbl): ?>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" class="form-check-input" id="acc_<?= $val ?>" name="<?= $val ?>" value="1"
                               <?= !empty($accessibility[$val]) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="acc_<?= $val ?>" style="font-size:.85rem"><?= $lbl ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium d-block">Security features</label>
                    <?php foreach (['gated' => 'Gated', 'fenced' => 'Fenced', 'security_guard' => 'Guard',
                                    'cctv' => 'CCTV', 'street_lighting' => 'Street lighting', 'intercom' => 'Intercom'] as $val => $lbl): ?>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" class="form-check-input" id="sec_<?= $val ?>" name="<?= $val ?>" value="1"
                               <?= !empty($security[$val]) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="sec_<?= $val ?>" style="font-size:.85rem"><?= $lbl ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Amenities ──────────────────────────────────────── -->
    <?php if (!empty($options['amenities'])): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold border-0 py-3">Amenities</div>
        <div class="card-body">
            <div class="row g-2">
                <?php foreach ($options['amenities'] as $a): ?>
                <div class="col-6 col-md-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="am_<?= (int)$a['id'] ?>"
                               name="amenity_ids[]" value="<?= (int)$a['id'] ?>"
                               <?= in_array((int)$a['id'], $amenityIds, true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="am_<?= (int)$a['id'] ?>" style="font-size:.85rem">
                            <?= Security::e($a['name']) ?>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Images (edit mode) ─────────────────────────────── -->
    <?php if ($isEdit): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold border-0 py-3 d-flex justify-content-between align-items-center">
            <span>Photos</span>
            <a href="<?= Url::base('admin/properties/' . $propertyId) ?>#images" class="btn btn-sm btn-outline-secondary">
                Manage images
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($images)): ?>
            <p class="text-muted small mb-0">No photos yet — use the image manager on the property page to upload some.</p>
            <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach (array_slice($images, 0, 8) as $img): ?>
                <img src="<?= Url::image($img['thumbnail'] ?: $img['path']) ?>" alt=""
                     style="width:72px;height:56px;object-fit:cover;border-radius:6px;border:1.5px solid var(--border)">
                <?php endforeach; ?>
                <?php if (count($images) > 8): ?>
                <span class="align-self-center text-muted small">+<?= count($images) - 8 ?> more</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold border-0 py-3">Photos</div>
        <div class="card-body">
            <label class="form-label fw-medium">Upload images</label>
            <input type="file" name="images[]" class="form-control" multiple accept="image/jpeg,image/png,image/webp,image/gif">
            <div class="row g-2 mt-2">
                <div class="col-md-4">
                    <select name="image_category" class="form-select">
                        <?php foreach (['exterior' => 'Exterior', 'living_room' => 'Living room', 'bedroom' => 'Bedroom',
                                        'kitchen' => 'Kitchen', 'bathroom' => 'Bathroom', 'toilet' => 'Toilet',
                                        'porch' => 'Porch', 'compound' => 'Compound', 'parking' => 'Parking',
                                        'road' => 'Road', 'other' => 'Other'] as $v => $lbl): ?>
                        <option value="<?= $v ?>" <?= $v === 'exterior' ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8">
                    <div class="form-text mb-0">JPEG, PNG, WebP or GIF — up to 5 MB each. The first image becomes the cover photo.</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Submit ─────────────────────────────────────────── -->
    <div class="d-flex gap-2 mb-5">
        <button type="submit" class="btn btn-primary px-4">
            <?= $isEdit ? 'Save changes' : 'Create property' ?>
        </button>
        <a href="<?= Url::base('admin/properties') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<script>
/* ── Cascading location selects (uses the public location API) ── */
(function () {
    var region = document.getElementById('locRegion'),
        city   = document.getElementById('locCity'),
        area   = document.getElementById('locArea');
    if (!region || !city || !area) return;

    function fill(select, items, selectedId) {
        select.innerHTML = '<option value="">—</option>';
        items.forEach(function (it) {
            var o = document.createElement('option');
            o.value = it.id; o.textContent = it.name;
            if (selectedId && parseInt(o.value, 10) === parseInt(selectedId, 10)) o.selected = true;
            select.appendChild(o);
        });
    }

    function loadCities(keepArea) {
        var rid = region.value;
        city.innerHTML = '<option value="">—</option>';
        area.innerHTML = '<option value="">—</option>';
        if (!rid) return;
        fetch('<?= Url::base('api/cities') ?>/' + rid)
            .then(function (r) { return r.json(); })
            .then(function (j) {
                fill(city, j.data || [], city.getAttribute('data-selected'));
                if (city.getAttribute('data-selected')) { city.dataset.selected = ''; loadAreas(); }
            });
    }

    function loadAreas() {
        var cid = city.value;
        area.innerHTML = '<option value="">—</option>';
        if (!cid) return;
        fetch('<?= Url::base('api/areas') ?>/' + cid)
            .then(function (r) { return r.json(); })
            .then(function (j) { fill(area, j.data || [], area.getAttribute('data-selected')); });
    }

    region.addEventListener('change', function () { city.dataset.selected = ''; area.dataset.selected = ''; loadCities(); });
    city.addEventListener('change', function () { area.dataset.selected = ''; loadAreas(); });

    // Preselect chain on page load (edit mode)
    if (region.value) loadCities();
})();

/* ── Room dimension rows ── */
(function () {
    var wrap = document.getElementById('spaceRows');
    var addBtn = document.getElementById('addSpaceRow');
    var tmpl = document.getElementById('spaceTemplate');
    if (!wrap || !addBtn || !tmpl) return;

    function syncEmptyNote() {
        var note = document.getElementById('spacesEmptyNote');
        if (note) note.style.display = wrap.querySelectorAll('.space-row:not(.d-none)').length ? 'none' : 'block';
    }
    syncEmptyNote();

    addBtn.addEventListener('click', function () {
        var clone = tmpl.cloneNode(true);
        clone.removeAttribute('id');
        clone.classList.remove('d-none');
        wrap.appendChild(clone);
        clone.querySelector('input').focus();
        syncEmptyNote();
    });

    wrap.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-space')) {
            e.target.closest('.space-row').remove();
            syncEmptyNote();
        }
    });
})();
</script>
