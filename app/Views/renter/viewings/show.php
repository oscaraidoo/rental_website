<?php use App\Helpers\{Url, Security, Str}; ?>

<div class="row g-4 justify-content-center">
    <div class="col-12 col-lg-8">

        <!-- Status banner -->
        <?php
        $banners = [
            'pending_payment' => ['class'=>'warning', 'icon'=>'fa-clock',        'msg'=>'Viewing fee payment required to confirm this request.'],
            'paid'            => ['class'=>'info',    'icon'=>'fa-check-circle',  'msg'=>'Payment received. Awaiting confirmation from the agency.'],
            'confirmed'       => ['class'=>'success', 'icon'=>'fa-calendar-check','msg'=>'Your viewing is confirmed!'],
            'completed'       => ['class'=>'success', 'icon'=>'fa-flag-checkered','msg'=>'Viewing completed.'],
            'cancelled'       => ['class'=>'danger',  'icon'=>'fa-ban',           'msg'=>'This viewing has been cancelled.'],
        ];
        $banner = $banners[$viewing['status']] ?? ['class'=>'secondary','icon'=>'fa-info-circle','msg'=>ucwords(str_replace('_',' ',$viewing['status']))];
        ?>
        <div class="alert alert-<?= $banner['class'] ?> d-flex align-items-center gap-3 mb-4">
            <i class="fa-solid <?= $banner['icon'] ?> fs-4"></i>
            <div>
                <strong><?= ucwords(str_replace('_',' ',$viewing['status'])) ?></strong> — <?= $banner['msg'] ?>
                <?php if ($viewing['status'] === 'confirmed'): ?>
                <div class="mt-1 fw-semibold">
                    <?= date('D, d M Y', strtotime($viewing['confirmed_date'])) ?> at <?= date('H:i', strtotime($viewing['confirmed_time'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Viewing details card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0 py-3">Viewing Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="info-row"><span class="info-label">Reference</span><code><?= Security::e($viewing['reference']) ?></code></div>
                            <div class="info-row"><span class="info-label">Property</span><a href="<?= Url::base('properties/' . $viewing['property_slug']) ?>"><?= Security::e(Str::truncate($viewing['property_title'], 35)) ?></a></div>
                            <div class="info-row"><span class="info-label">Requested Date</span><span class="info-value"><?= date('D d M Y', strtotime($viewing['preferred_date'])) ?></span></div>
                            <div class="info-row"><span class="info-label">Requested Time</span><span class="info-value"><?= date('H:i', strtotime($viewing['preferred_time'])) ?></span></div>
                            <?php if ($viewing['alt_date']): ?>
                            <div class="info-row"><span class="info-label">Alt Date</span><span class="info-value text-muted"><?= date('D d M Y', strtotime($viewing['alt_date'])) ?></span></div>
                            <?php endif; ?>
                            <div class="info-row"><span class="info-label">Attendees</span><span class="info-value"><?= $viewing['num_attendees'] ?></span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="info-row"><span class="info-label">Viewing Fee</span><span class="info-value fw-bold text-primary"><?= Str::currency((float)$viewing['viewing_fee']) ?></span></div>
                            <div class="info-row"><span class="info-label">Fee Paid</span>
                                <?php if ($viewing['fee_paid']): ?>
                                <span class="verified-badge"><i class="fa-solid fa-check me-1"></i>Paid</span>
                                <?php else: ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($viewing['confirmed_date']): ?>
                            <div class="info-row"><span class="info-label">Confirmed Date</span><span class="info-value text-success"><?= date('D d M Y', strtotime($viewing['confirmed_date'])) ?></span></div>
                            <div class="info-row"><span class="info-label">Confirmed Time</span><span class="info-value text-success"><?= date('H:i', strtotime($viewing['confirmed_time'])) ?></span></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if ($viewing['special_request']): ?>
                <div class="mt-3">
                    <p class="text-muted small mb-1 fw-medium">Special Request</p>
                    <p class="mb-0"><?= Security::e($viewing['special_request']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex gap-3 flex-wrap mb-4">
            <?php if ($viewing['status'] === 'pending_payment'): ?>
            <form method="POST" action="<?= Url::base('renter/viewings/' . $viewing['id'] . '/pay') ?>" class="flex-fill">
                <?= Security::csrfField() ?>
                <button class="btn btn-success w-100 py-3 fw-semibold fs-5">
                    <i class="fa-solid fa-credit-card me-2"></i>Pay <?= Str::currency((float)$viewing['viewing_fee']) ?> to Confirm
                </button>
            </form>
            <?php endif; ?>

            <?php if (in_array($viewing['status'], ['pending_payment','paid','confirmed'])): ?>
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                <i class="fa-solid fa-ban me-2"></i>Cancel Viewing
            </button>
            <?php endif; ?>

            <?php if ($canReview): ?>
            <a href="<?= Url::base('renter/reviews/create/' . $viewing['id']) ?>" class="btn btn-primary">
                <i class="fa-solid fa-star me-2"></i>Leave a Review
            </a>
            <?php endif; ?>

            <a href="<?= Url::base('properties/' . $viewing['property_slug']) ?>" class="btn btn-outline-secondary">
                <i class="fa-solid fa-building me-2"></i>View Property
            </a>
        </div>

        <a href="<?= Url::base('renter/viewings') ?>" class="text-muted small">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to all viewings
        </a>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Cancel Viewing</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= Url::base('renter/viewings/' . $viewing['id'] . '/cancel') ?>">
            <?= Security::csrfField() ?>
            <div class="modal-body">
                <p class="small text-muted">Are you sure you want to cancel this viewing request?</p>
                <label class="form-label fw-medium">Reason (optional)</label>
                <textarea name="reason" class="form-control" rows="3" placeholder="Let us know why you're cancelling..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Viewing</button>
                <button type="submit" class="btn btn-danger">Cancel Viewing</button>
            </div>
        </form>
    </div></div>
</div>
