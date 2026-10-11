<?php use App\Helpers\{Url, Security, Str}; ?>

<div class="row justify-content-center g-4">
    <div class="col-12 col-lg-7">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <!-- Property summary -->
                <div class="d-flex gap-3 align-items-start mb-4">
                    <?php $img = !empty($property['images']) ? $property['images'][0] : null; ?>
                    <?php if ($img): ?>
                    <img src="<?= Url::image($img['thumbnail'] ?: $img['path']) ?>" class="rounded-3 flex-shrink-0"
                         style="width:80px;height:64px;object-fit:cover" alt="">
                    <?php endif; ?>
                    <div>
                        <h5 class="fw-bold mb-1"><?= Security::e($property['title']) ?></h5>
                        <p class="text-muted small mb-1">
                            <i class="fa-solid fa-location-dot me-1"></i>
                            <?= Security::e($property['city_name'] ?? '') ?>, <?= Security::e($property['region_name'] ?? '') ?>
                        </p>
                        <div class="fw-bold text-primary"><?= Str::currency((float)$property['rent_monthly']) ?><span class="text-muted fw-normal small">/month</span></div>
                    </div>
                </div>

                <!-- Viewing fee notice -->
                <div class="viewing-fee-badge mb-4">
                    <div class="small opacity-75 mb-1">Viewing Fee</div>
                    <div class="fee-amount"><?= Str::currency($viewingFee) ?></div>
                    <div class="small opacity-75 mt-1">Payable after submitting this request</div>
                </div>

                <form method="POST" action="<?= Url::base('renter/viewings/request') ?>">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="property_id" value="<?= $property['id'] ?>">

                    <!-- Available slots or custom date -->
                    <?php if (!empty($slots)): ?>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Available Slots</label>
                        <div class="d-flex flex-column gap-2" id="slotsContainer">
                            <?php foreach ($slots as $slot): ?>
                            <label class="border rounded-3 p-3 d-flex align-items-center gap-3 cursor-pointer slot-option" style="cursor:pointer">
                                <input type="radio" name="preferred_date" value="<?= Security::e($slot['slot_date']) ?>"
                                       class="form-check-input slot-date-radio" required>
                                <input type="hidden" name="preferred_time" value="<?= Security::e($slot['slot_time']) ?>">
                                <div>
                                    <div class="fw-medium"><?= date('D, d M Y', strtotime($slot['slot_date'])) ?></div>
                                    <div class="text-muted small"><?= date('H:i', strtotime($slot['slot_time'])) ?> &bull; Max <?= $slot['max_viewers'] ?> viewers</div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-check mt-3">
                            <input type="checkbox" class="form-check-input" id="customDateToggle">
                            <label class="form-check-label" for="customDateToggle">Prefer a different date/time</label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Custom date/time -->
                    <div id="customDateSection" <?= !empty($slots) ? 'class="d-none"' : '' ?>>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Preferred Date <span class="text-danger">*</span></label>
                                <input type="date" name="preferred_date" class="form-control"
                                       min="<?= $minDate ?>" max="<?= $maxDate ?>" <?= empty($slots) ? 'required' : '' ?>>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Preferred Time <span class="text-danger">*</span></label>
                                <input type="time" name="preferred_time" class="form-control"
                                       min="08:00" max="18:00" <?= empty($slots) ? 'required' : '' ?>>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Alternative Date</label>
                                <input type="date" name="alt_date" class="form-control" min="<?= $minDate ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Alternative Time</label>
                                <input type="time" name="alt_time" class="form-control" min="08:00" max="18:00">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Number of Attendees</label>
                        <select name="num_attendees" class="form-select">
                            <option value="1">1 person (just me)</option>
                            <option value="2">2 people</option>
                            <option value="3">3 people</option>
                            <option value="4">4 people</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">Special Requests <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="special_request" class="form-control" rows="3"
                                  placeholder="Any specific questions or requests for the viewing..."></textarea>
                    </div>

                    <div class="alert alert-info small">
                        <i class="fa-solid fa-info-circle me-2"></i>
                        After submitting, you'll need to pay the viewing fee of <strong><?= Str::currency($viewingFee) ?></strong> to confirm your appointment.
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold fs-5">
                        <i class="fa-solid fa-calendar-check me-2"></i>Submit Viewing Request
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Property quick info -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold border-0">What to expect</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2 small"><i class="fa-solid fa-check text-success me-2"></i>Accompanied viewing with our agent</li>
                    <li class="mb-2 small"><i class="fa-solid fa-check text-success me-2"></i>Property keys available on the day</li>
                    <li class="mb-2 small"><i class="fa-solid fa-check text-success me-2"></i>All your questions answered</li>
                    <li class="mb-2 small"><i class="fa-solid fa-check text-success me-2"></i>No commitment required</li>
                    <li class="mb-2 small"><i class="fa-solid fa-info-circle text-primary me-2"></i>Cancel up to 24 hours before</li>
                </ul>
            </div>
        </div>

        <!-- Accessibility info -->
        <?php if (!empty($property['accessibility'])): ?>
        <?php $acc = $property['accessibility']; ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold border-0">Getting There</div>
            <div class="card-body">
                <div class="info-row"><span class="info-label"><i class="fa-solid fa-person-walking me-2 text-primary"></i>Walk time</span><span class="info-value"><?= Str::walkingTime($acc['walking_time_minutes'] ? (int)$acc['walking_time_minutes'] : null) ?></span></div>
                <div class="info-row"><span class="info-label"><i class="fa-solid fa-car me-2 text-primary"></i>Vehicle access</span><span class="info-value <?= $acc['vehicle_accessible'] ? 'text-success' : 'text-danger' ?>"><?= $acc['vehicle_accessible'] ? 'Yes' : 'Limited' ?></span></div>
                <div class="info-row"><span class="info-label"><i class="fa-solid fa-road me-2 text-primary"></i>Main road</span><span class="info-value"><?= Str::distance($acc['distance_main_road_km'] ? (float)$acc['distance_main_road_km'] : null) ?></span></div>
                <?php if ($acc['accessibility_notes']): ?>
                <p class="text-muted small mt-2 mb-0"><?= Security::e($acc['accessibility_notes']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('customDateToggle')?.addEventListener('change', function() {
    document.getElementById('customDateSection').classList.toggle('d-none', !this.checked);
    // Make custom date/time required when shown
    document.querySelectorAll('#customDateSection input[type="date"], #customDateSection input[type="time"]').forEach((el, i) => {
        if (i < 2) el.required = this.checked;
    });
    // Clear slot selections when switching to custom
    if (this.checked) {
        document.querySelectorAll('.slot-date-radio').forEach(r => r.checked = false);
    }
});
</script>
