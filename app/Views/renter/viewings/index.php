<?php use App\Helpers\{Url, Security, Str}; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h5 class="fw-bold mb-0">My Viewings</h5><p class="text-muted small mb-0"><?= $pager->info() ?></p></div>
    <a href="<?= Url::base('properties') ?>" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-search me-1"></i>Find Property
    </a>
</div>

<?php if (empty($viewings)): ?>
<div class="card border-0 shadow-sm text-center py-5">
    <i class="fa-solid fa-calendar-xmark text-muted mb-3" style="font-size:3rem"></i>
    <h5 class="fw-bold">No viewing requests yet</h5>
    <p class="text-muted mb-4">When you request a physical viewing of a property, it will appear here.</p>
    <a href="<?= Url::base('properties') ?>" class="btn btn-primary px-4">Browse Properties</a>
</div>
<?php else: ?>
<div class="d-flex flex-column gap-3">
    <?php foreach ($viewings as $v): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-1">
                    <?php if (!empty($v['property_thumb'])): ?>
                    <img src="<?= Url::image($v['property_thumb']) ?>" class="rounded-2 w-100"
                         style="height:60px;object-fit:cover" alt="">
                    <?php else: ?>
                    <div class="bg-light rounded-2 d-flex align-items-center justify-content-center" style="height:60px">
                        <i class="fa-solid fa-building text-muted"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-md-5">
                    <h6 class="fw-bold mb-1"><?= Security::e(Str::truncate($v['property_title'], 40)) ?></h6>
                    <div class="text-muted small">
                        <code><?= Security::e($v['reference']) ?></code> &bull;
                        <?= Security::e($v['city_name'] ?? '') ?>
                    </div>
                </div>
                <div class="col-6 col-md-2 text-center">
                    <div class="small text-muted">Requested Date</div>
                    <div class="fw-semibold small"><?= date('d M Y', strtotime($v['preferred_date'])) ?></div>
                    <div class="text-muted" style="font-size:.75rem"><?= date('H:i', strtotime($v['preferred_time'])) ?></div>
                    <?php if (!empty($v['confirmed_date']) && $v['status'] === 'confirmed'): ?>
                    <div class="text-success mt-1" style="font-size:.72rem">
                        <i class="fa-solid fa-check me-1"></i>Confirmed: <?= date('d M', strtotime($v['confirmed_date'])) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-6 col-md-2 text-center">
                    <div class="small text-muted">Fee</div>
                    <div class="fw-semibold"><?= Str::currency((float)$v['viewing_fee']) ?></div>
                    <?php if ($v['fee_paid']): ?>
                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.68rem">Paid</span>
                    <?php else: ?>
                    <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:.68rem">Unpaid</span>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2 justify-content-md-end">
                    <a href="<?= Url::base('renter/viewings/' . $v['id']) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <?php if ($v['status'] === 'pending_payment'): ?>
                    <form method="POST" action="<?= Url::base('renter/viewings/' . $v['id'] . '/pay') ?>">
                        <?= Security::csrfField() ?>
                        <button class="btn btn-sm btn-success">Pay Now</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($v['status'] === 'pending_payment'): ?>
            <div class="alert alert-warning py-2 px-3 mb-0 mt-3 small">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <strong>Action required:</strong> Pay the viewing fee of <?= Str::currency((float)$v['viewing_fee']) ?> to confirm this viewing.
            </div>
            <?php endif; ?>
            <div class="mt-2 d-flex align-items-center justify-content-between">
                <span class="badge-status status-<?= Security::e($v['status']) ?>"><?= ucwords(str_replace('_',' ',$v['status'])) ?></span>
                <span class="text-muted" style="font-size:.72rem">Requested <?= date('d M Y', strtotime($v['created_at'] ?? 'now')) ?></span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php if ($pager->lastPage > 1): ?><div class="mt-4"><?= $pager->render() ?></div><?php endif; ?>
<?php endif; ?>
