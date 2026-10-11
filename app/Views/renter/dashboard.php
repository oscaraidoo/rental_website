<?php use App\Helpers\{Url, Security, Str}; ?>

<!-- Welcome -->
<div class="welcome-card">
    <div>
        <h2>Welcome back, <?= Security::e(explode(' ', $_SESSION['user_name'])[0]) ?></h2>
        <p>Find your next home — see it, understand it, then decide.</p>
    </div>
    <a href="<?= Url::base('properties') ?>"
       class="btn btn-sm"
       style="background:rgba(255,255,255,.15);color:#fff;
              border:1.5px solid rgba(255,255,255,.25);white-space:nowrap;flex-shrink:0">
        Search properties
    </a>
</div>

<!-- Stats -->
<div class="renter-stats-grid">
    <?php
    $sc = [
        ['label'=>'Saved',           'value'=>$stats['saved'],              'url'=>'renter/saved'],
        ['label'=>'Comparing',       'value'=>$stats['compare'],            'url'=>'renter/compare'],
        ['label'=>'Total Viewings',  'value'=>$stats['viewings_total'],     'url'=>'renter/viewings'],
        ['label'=>'Upcoming',        'value'=>$stats['viewings_upcoming'],  'url'=>'renter/viewings'],
    ];
    foreach ($sc as $c): ?>
    <a href="<?= Url::base($c['url']) ?>" style="text-decoration:none">
        <div class="qs-card" style="cursor:pointer">
            <div class="qs-card-label"><?= Security::e($c['label']) ?></div>
            <div class="qs-card-value"><?= number_format((int)$c['value']) ?></div>
            <div class="qs-card-accent"></div>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<div class="renter-dash-grid">

    <!-- Saved properties -->
    <div class="table-card">
        <div class="table-card-head">
            <span class="table-card-title">Saved Properties</span>
            <a href="<?= Url::base('renter/saved') ?>" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <?php if (empty($savedProperties)): ?>
        <div class="empty-state">
            <div class="empty-state-title">Nothing saved yet</div>
            <div class="empty-state-sub">Browse properties and save ones you like.</div>
            <a href="<?= Url::base('properties') ?>" class="btn btn-primary btn-sm">Browse properties</a>
        </div>
        <?php else: ?>
        <div style="padding:0 20px">
            <?php foreach ($savedProperties as $p): ?>
            <div class="prop-row">
                <div class="prop-row-thumb" style="border-radius:6px;overflow:hidden;flex-shrink:0">
                    <?php if (!empty($p['primary_thumb'])): ?>
                    <img src="<?= Url::base($p['primary_thumb']) ?>"
                         style="width:100%;height:100%;object-fit:cover" alt="">
                    <?php else: ?>
                    <div style="width:100%;height:100%;background:var(--light)"></div>
                    <?php endif; ?>
                </div>
                <div style="flex:1;min-width:0">
                    <div class="prop-row-title" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= Security::e($p['title']) ?>
                    </div>
                    <div class="prop-row-meta" style="display:flex;align-items:center;gap:8px;margin-top:3px">
                        <span style="font-size:12px;color:var(--muted)"><?= Security::e($p['city_name'] ?? '') ?></span>
                        <span class="status-pill status-<?= Security::e($p['status']) ?>" style="font-size:10px">
                            <?= ucwords(str_replace('_',' ',$p['status'])) ?>
                        </span>
                    </div>
                </div>
                <div class="prop-row-price"><?= Str::currency((float)$p['rent_monthly']) ?></div>
                <a href="<?= Url::base('properties/' . $p['slug']) ?>"
                   class="btn btn-secondary btn-sm" style="flex-shrink:0">View</a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right column -->
    <div style="display:flex;flex-direction:column;gap:16px">

        <!-- Recent viewings -->
        <div class="table-card">
            <div class="table-card-head">
                <span class="table-card-title">My Viewings</span>
                <a href="<?= Url::base('renter/viewings') ?>" class="btn btn-secondary btn-sm">All</a>
            </div>
            <?php if (empty($recentViewings)): ?>
            <div style="padding:32px 20px;text-align:center;font-size:13px;color:var(--muted)">
                No viewings yet.
            </div>
            <?php else: ?>
            <div style="padding:0 20px">
                <?php foreach ($recentViewings as $v): ?>
                <a href="<?= Url::base('renter/viewings/' . $v['id']) ?>"
                   style="display:block;padding:13px 0;border-bottom:1px solid var(--border);
                          text-decoration:none;color:inherit">
                    <div style="display:flex;justify-content:space-between;gap:8px;margin-bottom:4px">
                        <div style="font-size:13px;font-weight:700;color:var(--black);
                                    flex:1;min-width:0;white-space:nowrap;
                                    overflow:hidden;text-overflow:ellipsis">
                            <?= Security::e(Str::truncate($v['property_title'], 28)) ?>
                        </div>
                        <span class="status-pill status-<?= Security::e($v['status']) ?>"
                              style="font-size:10px;white-space:nowrap;flex-shrink:0">
                            <?= ucwords(str_replace('_',' ',$v['status'])) ?>
                        </span>
                    </div>
                    <div style="font-size:12px;color:var(--muted)">
                        <span class="code" style="font-size:11px"><?= Security::e($v['reference']) ?></span>
                        &nbsp;&middot;&nbsp;
                        <?= date('d M Y', strtotime($v['preferred_date'])) ?>
                    </div>
                    <?php if ($v['status'] === 'pending_payment'): ?>
                    <div style="margin-top:6px;font-size:12px;font-weight:600;
                                background:#fffbeb;border:1px solid #fde68a;
                                color:#92400e;padding:4px 10px;border-radius:4px;
                                display:inline-block">
                        Payment required — <?= Str::currency((float)$v['viewing_fee']) ?>
                    </div>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent payments -->
        <?php if (!empty($recentPayments)): ?>
        <div class="table-card">
            <div class="table-card-head">
                <span class="table-card-title">Recent Payments</span>
                <a href="<?= Url::base('renter/payments') ?>" class="btn btn-secondary btn-sm">All</a>
            </div>
            <div style="padding:0 20px">
                <?php foreach ($recentPayments as $pay): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;
                            padding:12px 0;border-bottom:1px solid var(--border)">
                    <div>
                        <div style="font-size:13px;font-weight:600;color:var(--black)">
                            <?= ucwords(str_replace('_',' ',$pay['purpose'])) ?>
                        </div>
                        <div style="font-size:11px;color:var(--muted)">
                            <?= date('d M Y', strtotime($pay['created_at'])) ?>
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:14px;font-weight:800;letter-spacing:-.02em">
                            <?= Str::currency((float)$pay['amount']) ?>
                        </div>
                        <span class="status-pill status-<?= Security::e($pay['status']) ?>"
                              style="font-size:10px">
                            <?= ucfirst($pay['status']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
