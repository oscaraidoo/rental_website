<?php use App\Helpers\{Url, Security}; ?>

<div class="row justify-content-center">
<div class="col-12 col-lg-8">
<form method="POST" action="<?= Url::base('admin/settings/fees') ?>">
    <?= Security::csrfField() ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold border-0 py-3">
            <i class="fa-solid fa-coins text-warning me-2"></i>Fee & Commission Configuration
        </div>
        <div class="card-body">
            <div class="alert alert-info small">
                <i class="fa-solid fa-info-circle me-2"></i>
                All percentage values are applied to the agreed annual/monthly rent. Changes take effect immediately for new transactions.
            </div>

            <h6 class="fw-bold text-muted text-uppercase small mb-3 mt-4">Agency Commission</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Total Commission Rate (%)</label>
                    <div class="input-group">
                        <input type="number" name="commission_rate" class="form-control" step="0.01" min="0" max="100"
                               value="<?= Security::e($settings['commission_rate']['value'] ?? '5.00') ?>">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text">Of annual rent</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Landlord Portion (%)</label>
                    <div class="input-group">
                        <input type="number" name="commission_landlord_rate" class="form-control" step="0.01" min="0" max="100"
                               value="<?= Security::e($settings['commission_landlord_rate']['value'] ?? '2.50') ?>">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Renter Portion (%)</label>
                    <div class="input-group">
                        <input type="number" name="commission_renter_rate" class="form-control" step="0.01" min="0" max="100"
                               value="<?= Security::e($settings['commission_renter_rate']['value'] ?? '2.50') ?>">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold text-muted text-uppercase small mb-3">Viewing Fees</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Default Viewing Fee (GH₵)</label>
                    <div class="input-group">
                        <span class="input-group-text">GH₵</span>
                        <input type="number" name="viewing_fee_default" class="form-control" step="0.01" min="0"
                               value="<?= Security::e($settings['viewing_fee_default']['value'] ?? '50.00') ?>">
                    </div>
                    <div class="form-text">Charged to renter before physical viewing</div>
                </div>
            </div>

            <h6 class="fw-bold text-muted text-uppercase small mb-3">Landlord Contact Unlock</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Unlock Fee (GH₵)</label>
                    <div class="input-group">
                        <span class="input-group-text">GH₵</span>
                        <input type="number" name="landlord_contact_fee" class="form-control" step="0.01" min="0"
                               value="<?= Security::e($settings['landlord_contact_fee']['value'] ?? '50.00') ?>">
                    </div>
                    <div class="form-text">One-time fee renters pay to view the landlord's phone, email &amp; address for a property</div>
                </div>
            </div>

            <h6 class="fw-bold text-muted text-uppercase small mb-3">Listing Fees</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Registration Fee (GH₵)</label>
                    <div class="input-group">
                        <span class="input-group-text">GH₵</span>
                        <input type="number" name="registration_fee" class="form-control" step="0.01" min="0"
                               value="<?= Security::e($settings['registration_fee']['value'] ?? '100.00') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Verification Fee (GH₵)</label>
                    <div class="input-group">
                        <span class="input-group-text">GH₵</span>
                        <input type="number" name="verification_fee" class="form-control" step="0.01" min="0"
                               value="<?= Security::e($settings['verification_fee']['value'] ?? '150.00') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Featured Listing Fee (GH₵)</label>
                    <div class="input-group">
                        <span class="input-group-text">GH₵</span>
                        <input type="number" name="featured_listing_fee" class="form-control" step="0.01" min="0"
                               value="<?= Security::e($settings['featured_listing_fee']['value'] ?? '200.00') ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            <button type="submit" class="btn btn-warning fw-semibold px-4">
                <i class="fa-solid fa-save me-2"></i>Save Fee Configuration
            </button>
        </div>
    </div>
</form>
</div>
</div>
