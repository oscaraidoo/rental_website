<?php use App\Helpers\{Url, Security, Str}; ?>

<nav style="font-size:13px;color:#5a7a6e;margin-bottom:20px">
    <a href="<?= Url::base('renter') ?>" style="color:#5a7a6e">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="<?= Url::base('properties/' . ($property['slug'] ?? '')) ?>" style="color:#5a7a6e"><?= Security::e($property['title'] ?? 'Property') ?></a>
    <span class="mx-2">/</span>
    <span style="color:#051F20;font-weight:600">Landlord Contact</span>
</nav>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h4 class="fw-bold mb-1"><?= Security::e($property['title']) ?></h4>
                <p class="text-muted mb-0">
                    <code><?= Security::e($property['reference']) ?></code> &bull;
                    <?= Security::e($property['type_name'] ?? '') ?> &bull;
                    <?= Security::e($property['city_name'] ?? '—') ?>
                </p>
            </div>
            <div class="text-end">
                <div class="h4 fw-bold text-primary mb-0"><?= Str::currency((float)($property['rent_monthly'] ?? 0)) ?><small class="fs-6 fw-normal text-muted">/mo</small></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold border-0 d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-address-book text-primary me-2"></i>Landlord Contact Details</span>
        <?php if (!empty($unlock['paid_at'])): ?>
        <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Unlocked</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 rounded" style="background:var(--c-surface,#f8faf9)">
                    <div class="text-muted small mb-1">Landlord Name</div>
                    <div class="fw-semibold"><?= Security::e($contact['name'] ?: '—') ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded" style="background:var(--c-surface,#f8faf9)">
                    <div class="text-muted small mb-1">Phone</div>
                    <div class="fw-semibold">
                        <?php if (!empty($contact['phone'])): ?>
                            <a href="tel:<?= Security::e($contact['phone']) ?>" style="color:#235347"><?= Security::e($contact['phone']) ?></a>
                        <?php else: ?>—<?php endif; ?>
                        <?php if (!empty($contact['phone2'])): ?>
                            <br><a href="tel:<?= Security::e($contact['phone2']) ?>" style="color:#235347"><?= Security::e($contact['phone2']) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded" style="background:var(--c-surface,#f8faf9)">
                    <div class="text-muted small mb-1">Email</div>
                    <div class="fw-semibold">
                        <?php if (!empty($contact['email'])): ?>
                            <a href="mailto:<?= Security::e($contact['email']) ?>" style="color:#235347"><?= Security::e($contact['email']) ?></a>
                        <?php else: ?>—<?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded" style="background:var(--c-surface,#f8faf9)">
                    <div class="text-muted small mb-1">Address</div>
                    <div class="fw-semibold"><?= Security::e($contact['address'] ?: '—') ?></div>
                </div>
            </div>
        </div>

        <?php if (!empty($contact['notes'])): ?>
        <div class="mt-3 p-3 rounded" style="background:#fff8e1;border:1px solid #ffe082">
            <div class="text-muted small mb-1"><i class="fa-solid fa-circle-info me-1"></i>Notes from the agent</div>
            <div><?= Security::e($contact['notes']) ?></div>
        </div>
        <?php endif; ?>

        <?php if (empty($contact['phone']) && empty($contact['phone2']) && empty($contact['email']) && empty($contact['address'])): ?>
        <div class="alert alert-warning mb-0">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            The landlord's contact details have not been added for this property yet. Please contact support.
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <a href="<?= Url::base('properties/' . ($property['slug'] ?? '')) ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-2"></i>Back to property
    </a>
    <a href="<?= Url::base('renter/viewings/request/' . $property['id']) ?>" class="btn btn-primary">
        <i class="fa-solid fa-calendar-check me-2"></i>Request a Viewing
    </a>
</div>
