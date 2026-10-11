<?php use App\Helpers\{Url, Security, Str}; ?>

<div class="row g-4">

    <!-- Left: Property info -->
    <div class="col-12 col-lg-8">

        <!-- Header card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge-status status-<?= Security::e($property['status']) ?>"><?= ucwords(str_replace('_',' ',$property['status'])) ?></span>
                            <?php if ($property['verified']): ?>
                            <span class="verified-badge"><i class="fa-solid fa-circle-check"></i> Agency Verified</span>
                            <?php endif; ?>
                        </div>
                        <h4 class="fw-bold mb-1"><?= Security::e($property['title']) ?></h4>
                        <p class="text-muted mb-0">
                            <code><?= Security::e($property['reference']) ?></code> &bull;
                            <?= Security::e($property['type_name']) ?> &bull;
                            <?= Security::e($property['city_name'] ?? '—') ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <div class="h4 fw-bold text-primary mb-0"><?= Str::currency((float)$property['rent_monthly']) ?><small class="fs-6 fw-normal text-muted">/mo</small></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="card border-0 shadow-sm mb-4" id="images">
            <div class="card-header bg-white fw-bold border-0 d-flex justify-content-between align-items-center">
                <span>Property Images</span>
                <span class="badge bg-secondary"><?= count($property['images']) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($property['images'])): ?>
                <p class="text-muted small mb-3">No photos yet. Upload from your device or import from a web link below — the first image becomes the cover photo.</p>
                <?php else: ?>
                <div class="row g-3 mb-4">
                    <?php foreach ($property['images'] as $img): ?>
                    <div class="col-6 col-sm-4 col-md-3">
                        <div class="position-relative" style="height:110px;overflow:hidden;border-radius:8px;border:2px solid <?= $img['is_primary'] ? 'var(--c-primary,#235347)' : 'transparent' ?>">
                            <img src="<?= Url::image($img['thumbnail'] ?: $img['path']) ?>"
                                 alt="<?= Security::e($img['caption'] ?? 'Property image') ?>"
                                 class="w-100 h-100" style="object-fit:cover">
                            <?php if ($img['is_primary']): ?>
                            <span class="position-absolute top-0 start-0 badge bg-primary" style="font-size:.6rem">Cover</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-center mt-1" style="font-size:.7rem;color:#888"><?= Security::e(ucfirst($img['category'])) ?></div>
                        <div class="d-flex justify-content-center gap-1 mt-1">
                            <?php if (!$img['is_primary']): ?>
                            <form method="POST" action="<?= Url::base('admin/properties/images/' . $img['id'] . '/primary') ?>">
                                <?= Security::csrfField() ?>
                                <button type="submit" class="btn btn-sm btn-outline-secondary py-0" style="font-size:.68rem">Make cover</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= Url::base('admin/properties/images/' . $img['id'] . '/delete') ?>"
                                  data-confirm="Delete this image? This cannot be undone.">
                                <?= Security::csrfField() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0" style="font-size:.68rem">Delete</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="row g-3">
                    <!-- Upload from device -->
                    <div class="col-md-6">
                        <form method="POST" action="<?= Url::base('admin/properties/' . $property['id'] . '/images') ?>" enctype="multipart/form-data"
                              class="border rounded-3 p-3 h-100" style="background:var(--c-surface,#f8faf9)">
                            <?= Security::csrfField() ?>
                            <label class="form-label fw-semibold mb-2"><i class="fa-solid fa-upload me-1"></i> Upload from device</label>
                            <input type="file" name="images[]" class="form-control form-control-sm mb-2"
                                   multiple accept="image/jpeg,image/png,image/webp,image/gif" required>
                            <div class="row g-2">
                                <div class="col-7">
                                    <select name="image_category" class="form-select form-select-sm">
                                        <?php foreach (['exterior' => 'Exterior', 'living_room' => 'Living room', 'bedroom' => 'Bedroom',
                                                        'kitchen' => 'Kitchen', 'bathroom' => 'Bathroom', 'toilet' => 'Toilet',
                                                        'porch' => 'Porch', 'compound' => 'Compound', 'parking' => 'Parking',
                                                        'road' => 'Road', 'other' => 'Other'] as $v => $lbl): ?>
                                        <option value="<?= $v ?>" <?= $v === 'exterior' ? 'selected' : '' ?>><?= $lbl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-5">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Upload</button>
                                </div>
                            </div>
                            <div class="form-text mb-0" style="font-size:.7rem">JPEG, PNG, WebP or GIF — up to 5 MB each.</div>
                        </form>
                    </div>

                    <!-- Import from web URL -->
                    <div class="col-md-6">
                        <form method="POST" action="<?= Url::base('admin/properties/' . $property['id'] . '/images/import') ?>"
                              class="border rounded-3 p-3 h-100" style="background:var(--c-surface,#f8faf9)">
                            <?= Security::csrfField() ?>
                            <label class="form-label fw-semibold mb-2"><i class="fa-solid fa-cloud-arrow-down me-1"></i> Import from a web link</label>
                            <input type="url" name="image_url" class="form-control form-control-sm mb-2"
                                   placeholder="https://…/house-photo.jpg" required
                                   pattern="https?://.*" title="Paste a direct link to an image (https://…)">
                            <div class="row g-2">
                                <div class="col-7">
                                    <select name="image_category" class="form-select form-select-sm">
                                        <?php foreach (['exterior' => 'Exterior', 'living_room' => 'Living room', 'bedroom' => 'Bedroom',
                                                        'kitchen' => 'Kitchen', 'bathroom' => 'Bathroom', 'toilet' => 'Toilet',
                                                        'porch' => 'Porch', 'compound' => 'Compound', 'parking' => 'Parking',
                                                        'road' => 'Road', 'other' => 'Other'] as $v => $lbl): ?>
                                        <option value="<?= $v ?>" <?= $v === 'exterior' ? 'selected' : '' ?>><?= $lbl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-5">
                                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">Import</button>
                                </div>
                            </div>
                            <div class="form-text mb-0" style="font-size:.7rem">Paste the image's direct URL — the photo is downloaded and saved to this property. Hotlink images from Google Images often won't work; use the photo's own address.</div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0">Description</div>
            <div class="card-body">
                <p class="mb-0" style="white-space:pre-line"><?= Security::e($property['description']) ?></p>
            </div>
        </div>

        <!-- Dimensions -->
        <?php if (!empty($property['spaces'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0"><i class="fa-solid fa-ruler-combined text-primary me-2"></i>Room Dimensions</div>
            <div class="card-body">
                <div class="dimension-grid">
                    <?php foreach ($property['spaces'] as $space): ?>
                    <div class="dimension-card">
                        <div class="space-name"><?= Security::e($space['space_name']) ?></div>
                        <div class="dimensions">
                            <?= $space['length'] ?>m × <?= $space['width'] ?>m × <?= $space['height'] ?>m
                        </div>
                        <?php if ($space['area_sqm']): ?>
                        <div class="area"><?= number_format((float)$space['area_sqm'], 1) ?> m²</div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Accessibility -->
        <?php if (!empty($property['accessibility'])): ?>
        <?php $acc = $property['accessibility']; ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0"><i class="fa-solid fa-road text-primary me-2"></i>Road Accessibility</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3 text-center p-3 bg-light rounded">
                        <div class="h5 fw-bold"><?= $acc['walking_time_minutes'] ?? '—' ?> <small class="fs-6 fw-normal">min</small></div>
                        <div class="small text-muted"><i class="fa-solid fa-person-walking text-primary me-1"></i>Walking time</div>
                    </div>
                    <div class="col-6 col-md-3 text-center p-3 bg-light rounded">
                        <div class="h5 fw-bold"><?= $acc['distance_main_road_km'] ?? '—' ?> <small class="fs-6 fw-normal">km</small></div>
                        <div class="small text-muted"><i class="fa-solid fa-road text-primary me-1"></i>Main road</div>
                    </div>
                    <div class="col-6 col-md-3 text-center p-3 <?= $acc['vehicle_accessible'] ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10' ?> rounded">
                        <div class="h5 fw-bold <?= $acc['vehicle_accessible'] ? 'text-success' : 'text-danger' ?>">
                            <?= $acc['vehicle_accessible'] ? 'Yes' : 'No' ?>
                        </div>
                        <div class="small text-muted"><i class="fa-solid fa-car me-1"></i>Vehicle access</div>
                    </div>
                    <div class="col-6 col-md-3 text-center p-3 bg-light rounded">
                        <div class="h5 fw-bold"><?= ucfirst($acc['road_condition'] ?? '—') ?></div>
                        <div class="small text-muted"><i class="fa-solid fa-triangle-exclamation me-1"></i>Road type</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php include ROOT_PATH . '/app/Views/admin/properties/_condition_card.php'; ?>

        <!-- Status history -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0">Status History</div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($statusHistory as $h): ?>
                    <div class="timeline-item">
                        <div class="fw-medium">
                            <?= $h['old_status'] ? Security::e(ucwords(str_replace('_',' ',$h['old_status']))) . ' → ' : '' ?>
                            <span class="badge-status status-<?= Security::e($h['new_status']) ?>"><?= ucwords(str_replace('_',' ',$h['new_status'])) ?></span>
                        </div>
                        <?php if ($h['reason']): ?>
                        <div class="text-muted small"><?= Security::e($h['reason']) ?></div>
                        <?php endif; ?>
                        <div class="time"><?= Security::e($h['changed_by_name'] ?? 'System') ?> &bull; <?= date('d M Y H:i', strtotime($h['created_at'])) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Actions -->
    <div class="col-12 col-lg-4">

        <!-- Quick actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0">Admin Actions</div>
            <div class="card-body d-grid gap-2">

                <?php if (in_array($property['status'], ['submitted','under_review','inspection_required'])): ?>
                <a href="<?= Url::base('admin/verifications/' . $property['id']) ?>" class="btn btn-warning fw-semibold">
                    <i class="fa-solid fa-shield-check me-2"></i>Review & Verify
                </a>
                <?php endif; ?>

                <?php if ($property['status'] === 'verified' && !$property['is_featured']): ?>
                <button class="btn btn-outline-warning fw-semibold" data-bs-toggle="modal" data-bs-target="#featureModal">
                    <i class="fa-solid fa-star me-2"></i>Feature This Property
                </button>
                <?php endif; ?>

                <?php if (!in_array($property['status'], ['rejected','suspended'])): ?>
                <button class="btn btn-outline-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fa-solid fa-ban me-2"></i>
                    <?= $property['status'] === 'verified' ? 'Suspend' : 'Reject' ?>
                </button>
                <?php endif; ?>

                <!-- Hard delete — only for draft/rejected/suspended -->
                <?php if (in_array($property['status'], ['draft','rejected','suspended'])): ?>
                <form method="POST" action="<?= Url::base('admin/properties/' . $property['id'] . '/delete') ?>">
                    <?= \App\Helpers\Security::csrfField() ?>
                    <button type="submit"
                            class="btn btn-danger fw-semibold w-100"
                            data-confirm="Permanently delete &quot;<?= Security::e($property['title']) ?>&quot;? This cannot be undone.">
                        Delete Property
                    </button>
                </form>
                <?php endif; ?>

                <a href="<?= Url::base('properties/' . $property['slug']) ?>" target="_blank" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-external-link me-2"></i>View Public Page
                </a>
            </div>
        </div>

        <!-- Property details -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0">Property Details</div>
            <div class="card-body p-0">
                <?php
                $details = [
                    'Bedrooms'    => $property['num_bedrooms'],
                    'Bathrooms'   => $property['num_bathrooms'],
                    'Toilets'     => $property['num_toilets'],
                    'Kitchens'    => $property['num_kitchens'],
                    'Furnished'   => ucfirst(str_replace('_',' ',$property['furnished'])),
                    'Self-Contained'=> $property['is_self_contained'] ? 'Yes' : 'No',
                    'Floor'       => ucfirst($property['building_level']),
                    'Ceiling'     => $property['ceiling_name'] ?? '—',
                    'Flooring'    => $property['flooring_name'] ?? '—',
                    'Parking'     => $property['has_parking'] ? 'Yes' : 'No',
                    'Water'       => $property['water_available'] ? 'Available' : 'Not available',
                    'Pet Policy'  => ucwords(str_replace('_',' ',$property['pet_policy'])),
                ];
                ?>
                <div class="px-3 py-2">
                    <?php foreach ($details as $label => $val): ?>
                    <div class="info-row">
                        <span class="info-label"><?= $label ?></span>
                        <span class="info-value"><?= Security::e((string)$val) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Landlord info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0">Landlord</div>
            <div class="card-body">
                <p class="mb-1 fw-semibold"><?= Security::e($property['landlord_first'] . ' ' . $property['landlord_last']) ?></p>
                <p class="mb-1 small text-muted"><i class="fa-solid fa-envelope me-1"></i><?= Security::e($property['landlord_email']) ?></p>
                <p class="mb-1 small text-muted"><i class="fa-solid fa-phone me-1"></i><?= Security::e($property['landlord_phone'] ?? '—') ?></p>
                <?php if ($property['landlord_verified']): ?>
                <span class="badge bg-success">Verified Landlord</span>
                <?php else: ?>
                <span class="badge bg-secondary">Unverified</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Landlord contact details (posted by admin; renters pay to view) -->
        <div class="card border-0 shadow-sm mb-4" id="landlord-contact">
            <div class="card-header bg-white fw-bold border-0 d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-address-book text-primary me-2"></i>Landlord Contact Details</span>
                <span class="badge bg-light text-dark" style="font-size:.7rem">Renters pay 50 GHC to view</span>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= Url::base('admin/properties/' . $property['id'] . '/landlord-contact') ?>">
                    <?= Security::csrfField() ?>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Phone</label>
                        <input type="text" name="contact_phone" class="form-control form-control-sm"
                               value="<?= Security::e($property['landlord_contact_phone'] ?? '') ?>"
                               placeholder="e.g. 024 000 0000">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Alternate Phone</label>
                        <input type="text" name="contact_phone2" class="form-control form-control-sm"
                               value="<?= Security::e($property['landlord_contact_phone2'] ?? '') ?>"
                               placeholder="e.g. 020 000 0000">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Email</label>
                        <input type="email" name="contact_email" class="form-control form-control-sm"
                               value="<?= Security::e($property['landlord_contact_email'] ?? '') ?>"
                               placeholder="landlord@example.com">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Address</label>
                        <input type="text" name="contact_address" class="form-control form-control-sm"
                               value="<?= Security::e($property['landlord_contact_address'] ?? '') ?>"
                               placeholder="Office / home address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Notes (shown to renters)</label>
                        <textarea name="contact_notes" class="form-control form-control-sm" rows="2"
                                  placeholder="e.g. Call between 8am–6pm"><?= Security::e($property['landlord_contact_notes'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fa-solid fa-save me-1"></i>Save Contact Details
                    </button>
                </form>
            </div>
        </div>

        <!-- Verification badge -->
        <?php if ($property['verification']): ?>
        <?php $v = $property['verification']; ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0">Verification Record</div>
            <div class="card-body">
                <div class="info-row"><span class="info-label">Reference</span><code><?= Security::e($v['verification_ref']) ?></code></div>
                <div class="info-row"><span class="info-label">Status</span><span class="badge-status status-<?= $v['status'] === 'passed' ? 'verified' : 'rejected' ?>"><?= ucfirst($v['status']) ?></span></div>
                <div class="info-row"><span class="info-label">Score</span><span class="fw-bold"><?= $v['overall_score'] ?>%</span></div>
                <div class="info-row"><span class="info-label">Verifier</span><?= Security::e($v['verifier_name'] ?? '—') ?></div>
                <div class="info-row"><span class="info-label">Date</span><?= date('d M Y', strtotime($v['completed_at'] ?? $v['created_at'])) ?></div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Reject / Suspend Property</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= Url::base('admin/properties/' . $property['id'] . '/' . (in_array($property['status'],['verified','submitted']) ? 'reject' : 'suspend')) ?>">
            <?= \App\Helpers\Security::csrfField() ?>
            <div class="modal-body">
                <label class="form-label fw-medium">Reason (required)</label>
                <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm</button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="featureModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Feature Property</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= Url::base('admin/properties/' . $property['id'] . '/feature') ?>">
            <?= \App\Helpers\Security::csrfField() ?>
            <div class="modal-body">
                <label class="form-label fw-medium">Feature duration (days)</label>
                <input type="number" name="days" class="form-control" value="30" min="1" max="90">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">Feature Property</button>
            </div>
        </form>
    </div></div>
</div>
