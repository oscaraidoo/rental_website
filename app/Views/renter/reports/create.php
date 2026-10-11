<?php use App\Helpers\{Url, Security, Str}; ?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="fw-bold mb-0 text-danger"><i class="fa-solid fa-flag me-2"></i>Report Property</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning small mb-4">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Reporting: <strong><?= Security::e(Str::truncate($property['title'], 50)) ?></strong>
                </div>

                <form method="POST" action="<?= Url::base('renter/reports/store') ?>">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="property_id" value="<?= $property['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-medium">Report Type <span class="text-danger">*</span></label>
                        <select name="report_type" class="form-select" required>
                            <option value="">-- Select issue type --</option>
                            <?php
                            $types = [
                                'wrong_info'        => 'Wrong or inaccurate information',
                                'fake_property'     => 'Fake or non-existent property',
                                'wrong_dimensions'  => 'Wrong room dimensions',
                                'incorrect_rent'    => 'Incorrect rent amount',
                                'already_rented'    => 'Property already rented',
                                'misleading_photos' => 'Misleading or fake photos',
                                'unsafe'            => 'Unsafe environment',
                                'fraud'             => 'Suspected fraud or scam',
                                'other'             => 'Other',
                            ];
                            foreach ($types as $v => $l):
                            ?>
                            <option value="<?= $v ?>"><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="5" required minlength="20"
                                  placeholder="Please describe the issue in detail (minimum 20 characters)..."></textarea>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-danger px-4 fw-semibold">
                            <i class="fa-solid fa-flag me-2"></i>Submit Report
                        </button>
                        <a href="<?= Url::base('properties/' . $property['slug']) ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
