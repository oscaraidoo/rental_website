<?php use App\Helpers\{Url, Security, Str}; ?>
<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0 py-3">Viewing Details</div>
            <div class="card-body">
                <div class="info-row"><span class="info-label">Reference</span><code><?= Security::e($viewing['reference']) ?></code></div>
                <div class="info-row"><span class="info-label">Property</span><a href="<?= Url::base('admin/properties/' . $viewing['property_id']) ?>"><?= Security::e($viewing['property_title']) ?></a></div>
                <div class="info-row"><span class="info-label">Renter</span><?= Security::e($viewing['renter_name']) ?> &bull; <?= Security::e($viewing['renter_email']) ?></div>
                <div class="info-row"><span class="info-label">Preferred Date</span><?= date('D d M Y', strtotime($viewing['preferred_date'])) ?> at <?= date('H:i', strtotime($viewing['preferred_time'])) ?></div>
                <?php if ($viewing['confirmed_date']): ?>
                <div class="info-row"><span class="info-label">Confirmed Date</span><span class="text-success"><?= date('D d M Y', strtotime($viewing['confirmed_date'])) ?> at <?= date('H:i', strtotime($viewing['confirmed_time'])) ?></span></div>
                <?php endif; ?>
                <div class="info-row"><span class="info-label">Attendees</span><?= $viewing['num_attendees'] ?></div>
                <div class="info-row"><span class="info-label">Fee</span><strong><?= Str::currency((float)$viewing['viewing_fee']) ?></strong></div>
                <div class="info-row"><span class="info-label">Fee Paid</span><?= $viewing['fee_paid'] ? '<span class="text-success fw-semibold">Yes</span>' : '<span class="text-warning">No</span>' ?></div>
                <div class="info-row"><span class="info-label">Status</span><span class="badge-status status-<?= Security::e($viewing['status']) ?>"><?= ucwords(str_replace('_',' ',$viewing['status'])) ?></span></div>
                <?php if ($viewing['special_request']): ?>
                <div class="mt-3"><p class="text-muted small fw-medium mb-1">Special Request</p><p><?= Security::e($viewing['special_request']) ?></p></div>
                <?php endif; ?>
                <?php if ($viewing['cancellation_reason']): ?>
                <div class="mt-3"><p class="text-muted small fw-medium mb-1">Cancellation Reason</p><p class="text-danger"><?= Security::e($viewing['cancellation_reason']) ?></p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold border-0 py-3">Admin Actions</div>
            <div class="card-body d-grid gap-2">
                <?php if ($viewing['status'] === 'paid'): ?>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmViewingModal">
                    <i class="fa-solid fa-check me-2"></i>Confirm Viewing
                </button>
                <?php endif; ?>
                <?php if (!in_array($viewing['status'], ['completed','cancelled'])): ?>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelViewingModal">
                    <i class="fa-solid fa-ban me-2"></i>Cancel Viewing
                </button>
                <?php endif; ?>
                <?php if (in_array($viewing['status'], ['cancelled','no_show','pending_payment'])): ?>
                <form method="POST" action="<?= Url::base('admin/viewings/' . $viewing['id'] . '/delete') ?>">
                    <?= Security::csrfField() ?>
                    <button type="submit" class="btn btn-danger"
                            data-confirm="Permanently delete viewing <?= Security::e($viewing['reference']) ?>? This cannot be undone.">
                        Delete Permanently
                    </button>
                </form>
                <?php endif; ?>
                <a href="<?= Url::base('admin/viewings') ?>" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-2"></i>Back to Viewings
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmViewingModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Confirm Viewing</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= Url::base('admin/viewings/' . $viewing['id'] . '/confirm') ?>">
            <?= Security::csrfField() ?>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label fw-medium">Date</label><input type="date" name="confirmed_date" class="form-control" value="<?= Security::e($viewing['preferred_date']) ?>" required></div>
                <div><label class="form-label fw-medium">Time</label><input type="time" name="confirmed_time" class="form-control" value="<?= Security::e($viewing['preferred_time']) ?>" required></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success" type="submit">Confirm</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="cancelViewingModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Cancel Viewing</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= Url::base('admin/viewings/' . $viewing['id'] . '/cancel') ?>">
            <?= Security::csrfField() ?>
            <div class="modal-body"><label class="form-label fw-medium">Reason</label><textarea name="reason" class="form-control" rows="3" required></textarea></div>
            <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Back</button><button class="btn btn-danger" type="submit">Cancel Viewing</button></div>
        </form>
    </div></div>
</div>
