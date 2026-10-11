<?php use App\Helpers\{Url, Security}; ?>
<?php include __DIR__ . '/_layout.php'; ?>

<div class="prof-body">

    <!-- Email verification banner -->
    <?php if (empty($renter['email_verified'])): ?>
    <div class="prof-banner prof-banner--warn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>Your email address is not verified. Some features may be limited.</span>
        <form method="POST" action="<?= Url::base('renter/profile/resend-verification') ?>" style="margin:0">
            <?= Security::csrfField() ?>
            <button type="submit" class="prof-banner-btn">Verify now</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Missing fields tip -->
    <?php if (!empty($completion['missing']) && $completion['percent'] < 100): ?>
    <div class="prof-banner prof-banner--info">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>Complete your profile: <strong><?= implode(', ', array_slice($completion['missing'], 0, 3)) ?></strong><?= count($completion['missing']) > 3 ? ' and more.' : '.' ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= Url::base('renter/profile') ?>" id="profInfoForm">
        <?= Security::csrfField() ?>

        <!-- ── Personal Information ───────────────────────── -->
        <div class="prof-card">
            <div class="prof-card-head">
                <div>
                    <h3 class="prof-card-title">Personal Information</h3>
                    <p class="prof-card-sub">Your name, identity and contact details</p>
                </div>
                <button type="submit" class="prof-save-btn" form="profInfoForm">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Save changes
                </button>
            </div>
            <div class="prof-card-body">

                <div class="prof-grid-2">
                    <div class="prof-field">
                        <label>First name <span class="prof-req">*</span></label>
                        <input type="text" name="first_name" required maxlength="80"
                               value="<?= Security::e($renter['first_name'] ?? '') ?>">
                    </div>
                    <div class="prof-field">
                        <label>Last name <span class="prof-req">*</span></label>
                        <input type="text" name="last_name" required maxlength="80"
                               value="<?= Security::e($renter['last_name'] ?? '') ?>">
                    </div>
                </div>

                <div class="prof-grid-2">
                    <div class="prof-field">
                        <label>Email address</label>
                        <div class="prof-field-with-badge">
                            <input type="email" value="<?= Security::e($renter['email'] ?? '') ?>" disabled>
                            <?php if (!empty($renter['email_verified'])): ?>
                            <span class="prof-field-badge prof-field-badge--ok">Verified</span>
                            <?php else: ?>
                            <span class="prof-field-badge prof-field-badge--warn">Unverified</span>
                            <?php endif; ?>
                        </div>
                        <p class="prof-field-hint">Contact support to change your email address.</p>
                    </div>
                    <div class="prof-field">
                        <label>Phone number</label>
                        <div class="prof-field-with-badge">
                            <input type="tel" name="phone" placeholder="+233 20 000 0000" maxlength="30"
                                   value="<?= Security::e($renter['phone'] ?? '') ?>">
                            <?php if (!empty($renter['phone_verified'])): ?>
                            <span class="prof-field-badge prof-field-badge--ok">Verified</span>
                            <?php elseif (!empty($renter['phone'])): ?>
                            <span class="prof-field-badge prof-field-badge--warn">Unverified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="prof-grid-3">
                    <div class="prof-field">
                        <label>Gender</label>
                        <div class="prof-gender-group">
                            <?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not' => 'Prefer not to say'] as $val => $lbl): ?>
                            <label class="prof-gender-opt <?= ($renter['gender'] ?? '') === $val ? 'selected' : '' ?>">
                                <input type="radio" name="gender" value="<?= $val ?>"
                                       <?= ($renter['gender'] ?? '') === $val ? 'checked' : '' ?>
                                       onchange="this.closest('.prof-gender-group').querySelectorAll('.prof-gender-opt').forEach(l=>l.classList.remove('selected'));this.closest('.prof-gender-opt').classList.add('selected')">
                                <?= $lbl ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="prof-field">
                        <label>Date of birth</label>
                        <input type="date" name="date_of_birth"
                               value="<?= Security::e($renter['date_of_birth'] ?? '') ?>">
                    </div>
                </div>

            </div>
        </div>

        <!-- ── Address ────────────────────────────────────── -->
        <div class="prof-card">
            <div class="prof-card-head">
                <div>
                    <h3 class="prof-card-title">Location</h3>
                    <p class="prof-card-sub">Your residential address in Ghana</p>
                </div>
            </div>
            <div class="prof-card-body">
                <div class="prof-field" style="margin-bottom:16px">
                    <label>Street address</label>
                    <input type="text" name="address" placeholder="e.g. 14 Adenta Housing Road" maxlength="255"
                           value="<?= Security::e($renter['address'] ?? '') ?>">
                </div>
                <div class="prof-grid-2">
                    <div class="prof-field">
                        <label>City / Town</label>
                        <input type="text" name="city" placeholder="e.g. Accra" maxlength="100"
                               value="<?= Security::e($renter['city'] ?? '') ?>">
                    </div>
                    <div class="prof-field">
                        <label>Region</label>
                        <select name="region">
                            <option value="">Select region</option>
                            <?php foreach (['Greater Accra','Ashanti','Western','Central','Eastern','Volta','Northern','Upper East','Upper West','Brong-Ahafo','Western North','Ahafo','Bono East','Oti','Savannah','North East'] as $r): ?>
                            <option value="<?= $r ?>" <?= ($renter['region'] ?? '') === $r ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Rental Preferences ─────────────────────────── -->
        <div class="prof-card">
            <div class="prof-card-head">
                <div>
                    <h3 class="prof-card-title">Rental Preferences</h3>
                    <p class="prof-card-sub">Help landlords understand what you're looking for</p>
                </div>
            </div>
            <div class="prof-card-body">
                <div class="prof-grid-2">
                    <div class="prof-field">
                        <label>Occupation</label>
                        <input type="text" name="occupation" placeholder="e.g. Teacher, Engineer" maxlength="100"
                               value="<?= Security::e($renter['occupation'] ?? '') ?>">
                    </div>
                    <div class="prof-field">
                        <label>Employer</label>
                        <input type="text" name="employer" placeholder="Company or organisation" maxlength="191"
                               value="<?= Security::e($renter['employer'] ?? '') ?>">
                    </div>
                </div>
                <div class="prof-grid-3">
                    <div class="prof-field">
                        <label>Monthly income range</label>
                        <select name="monthly_income_range">
                            <option value="">Select range</option>
                            <?php foreach (['below_1000' => 'Below GH₵1,000', '1000_2000' => 'GH₵1,000 – 2,000', '2000_4000' => 'GH₵2,000 – 4,000', '4000_8000' => 'GH₵4,000 – 8,000', 'above_8000' => 'Above GH₵8,000'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= ($renter['monthly_income_range'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="prof-field">
                        <label>Budget min (GH₵)</label>
                        <input type="number" name="budget_min" step="50" min="0" placeholder="500"
                               value="<?= Security::e($renter['budget_min'] ?? '') ?>">
                    </div>
                    <div class="prof-field">
                        <label>Budget max (GH₵)</label>
                        <input type="number" name="budget_max" step="50" min="0" placeholder="2000"
                               value="<?= Security::e($renter['budget_max'] ?? '') ?>">
                    </div>
                </div>
                <div class="prof-grid-2">
                    <div class="prof-field">
                        <label>Number of occupants</label>
                        <input type="number" name="num_occupants" min="1" max="20"
                               value="<?= (int)($renter['num_occupants'] ?? 1) ?>">
                    </div>
                    <div class="prof-field">
                        <label>Preferred move-in</label>
                        <input type="date" name="preferred_move_in"
                               value="<?= Security::e($renter['preferred_move_in'] ?? '') ?>">
                    </div>
                </div>
                <div class="prof-field" style="margin-bottom:12px">
                    <label class="prof-toggle-label">
                        <input type="checkbox" name="has_pets" value="1"
                               <?= !empty($renter['has_pets']) ? 'checked' : '' ?>
                               onchange="document.getElementById('petWrap').style.display=this.checked?'block':'none'">
                        <span class="prof-toggle"></span>
                        I have pets
                    </label>
                </div>
                <div class="prof-field" id="petWrap" style="<?= empty($renter['has_pets']) ? 'display:none' : '' ?>">
                    <label>Describe your pets</label>
                    <input type="text" name="pet_description" placeholder="e.g. 1 small dog, 2 cats"
                           value="<?= Security::e($renter['pet_description'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- Mobile save button -->
        <div class="prof-save-row">
            <button type="submit" class="btn btn-primary">Save all changes</button>
            <a href="<?= Url::base('renter/dashboard') ?>" class="btn btn-secondary">Back to dashboard</a>
        </div>

    </form>
</div>
