<?php use App\Helpers\{Url, Security, Str}; ?>

<div class="row g-4">
    <!-- Checklist form -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0 py-3">
                <i class="fa-solid fa-clipboard-list text-primary me-2"></i>Verification Checklist
            </div>
            <div class="card-body">
                <form method="POST" action="<?= Url::base('admin/verifications/' . $property['id'] . '/complete') ?>">
                    <?= Security::csrfField() ?>

                    <div class="verify-checklist">
                        <?php
                        $checks = [
                            'dimensions_confirmed'    => 'Dimensions / measurements confirmed',
                            'images_confirmed'         => 'Photos verified (match actual property)',
                            'facilities_confirmed'     => 'Facilities / amenities confirmed',
                            'accessibility_confirmed'  => 'Road accessibility information verified',
                            'location_confirmed'       => 'Location / address confirmed',
                            'rent_confirmed'           => 'Rent amount confirmed',
                            'landlord_id_confirmed'    => 'Landlord identity confirmed',
                            'utilities_confirmed'      => 'Utilities (water, electricity) confirmed',
                            'availability_confirmed'   => 'Property availability confirmed',
                        ];
                        foreach ($checks as $name => $label):
                        $checked = $verification ? $verification[$name] : false;
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="<?= $name ?>" id="<?= $name ?>" value="1"
                                   <?= $checked ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $name ?>">
                                <?= Security::e($label) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4">
                        <label class="form-label fw-medium">Verification Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Internal notes about the verification..."><?= Security::e($verification['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-medium">Result</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="result" id="result_pass" value="passed" checked>
                                <label class="btn btn-outline-success w-100" for="result_pass">
                                    <i class="fa-solid fa-check me-1"></i>Pass (Publish)
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="result" id="result_fail" value="failed">
                                <label class="btn btn-outline-danger w-100" for="result_fail">
                                    <i class="fa-solid fa-times me-1"></i>Fail (Reject)
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4 py-2 fw-semibold">
                        <i class="fa-solid fa-shield-check me-2"></i>Complete Verification
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Property summary -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0">Property Summary</div>
            <div class="card-body p-0">
                <!-- Primary image -->
                <?php if (!empty($property['images'])): ?>
                <?php $img = $property['images'][0]; ?>
                <div style="height:180px;overflow:hidden">
                    <img src="<?= Url::image($img['path']) ?>" class="w-100 h-100" style="object-fit:cover">
                </div>
                <?php endif; ?>
                <div class="p-3">
                    <p class="fw-bold mb-1"><?= Security::e($property['title']) ?></p>
                    <p class="text-muted small mb-2"><code><?= Security::e($property['reference']) ?></code> &bull; <?= Security::e($property['type_name']) ?></p>

                    <div class="info-row"><span class="info-label">Rent</span><span class="info-value text-primary"><?= Str::currency((float)$property['rent_monthly']) ?>/mo</span></div>
                    <div class="info-row"><span class="info-label">Beds</span><span class="info-value"><?= $property['num_bedrooms'] ?></span></div>
                    <div class="info-row"><span class="info-label">Baths</span><span class="info-value"><?= $property['num_bathrooms'] ?></span></div>
                    <div class="info-row"><span class="info-label">City</span><span class="info-value"><?= Security::e($property['city_name'] ?? '—') ?></span></div>
                    <div class="info-row"><span class="info-label">Landlord</span><span class="info-value"><?= Security::e($property['landlord_first'] . ' ' . $property['landlord_last']) ?></span></div>

                    <?php if (!empty($property['accessibility'])): ?>
                    <?php $a = $property['accessibility']; ?>
                    <div class="info-row"><span class="info-label">Walk time</span><span class="info-value"><?= Str::walkingTime($a['walking_time_minutes'] ? (int)$a['walking_time_minutes'] : null) ?></span></div>
                    <div class="info-row"><span class="info-label">Vehicle access</span><span class="info-value <?= $a['vehicle_accessible'] ? 'text-success' : 'text-danger' ?>"><?= $a['vehicle_accessible'] ? 'Yes' : 'No' ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Dimensions -->
        <?php if (!empty($property['spaces'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0">Declared Dimensions</div>
            <div class="card-body">
                <div class="dimension-grid">
                    <?php foreach ($property['spaces'] as $s): ?>
                    <div class="dimension-card">
                        <div class="space-name"><?= Security::e($s['space_name']) ?></div>
                        <div class="dimensions"><?= $s['length'] ?>×<?= $s['width'] ?>×<?= $s['height'] ?><?= $s['unit'] ?></div>
                        <?php if ($s['area_sqm']): ?>
                        <div class="area"><?= number_format((float)$s['area_sqm'],1) ?>m²</div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <a href="<?= Url::base('admin/properties/' . $property['id']) ?>" class="btn btn-outline-secondary w-100">
            <i class="fa-solid fa-arrow-left me-2"></i>Back to Property
        </a>
    </div>
</div>
