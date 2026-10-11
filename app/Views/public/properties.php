<?php use App\Helpers\{Url, Security, Str}; ?>

<!-- Page header -->
<div style="background:#fff;border-bottom:1.5px solid var(--border);padding:28px 0">
    <div style="max-width:1400px;margin:0 auto;padding:0 32px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <h1 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:800;letter-spacing:-.03em;color:#051F20;margin-bottom:4px">Properties</h1>
            <p style="font-size:14px;color:#5a7a6e;margin:0">
                <?php if (!empty($filters['q'])): ?>Results for &ldquo;<?= Security::e($filters['q']) ?>&rdquo; &mdash; <?php endif; ?>
                <?= number_format($total) ?> verified <?= $total === 1 ? 'property' : 'properties' ?> available
            </p>
        </div>
        <!-- Mobile filter trigger -->
        <button class="ff-mobile-trigger" id="mobileFilterBtn" style="display:none"
                onclick="document.getElementById('ffDrawer').classList.add('open');document.body.style.overflow='hidden'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="20" y2="12"/><line x1="12" y1="18" x2="20" y2="18"/></svg>
            Filters
            <?php
            $activeCount = count(array_filter($filters, fn($v) => $v !== null && $v !== '' && $v !== [] && $v !== 'verified'));
            if ($activeCount): ?>
            <span class="ff-trigger-badge"><?= $activeCount ?></span>
            <?php endif; ?>
        </button>
    </div>
</div>

<div style="max-width:1400px;margin:0 auto;padding:32px 32px 80px;display:grid;grid-template-columns:280px 1fr;gap:28px;align-items:start">

    <!-- ── Desktop filter panel ──────────────────────────── -->
    <div id="desktopFilter">
        <div class="filter-panel">
            <div class="filter-panel-head">
                <h6 style="color:#235347;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em">Filters</h6>
                <?php if ($activeCount): ?>
                <a href="<?= Url::base('properties') ?>" style="font-size:12px;font-weight:600;color:#235347;text-decoration:underline">Clear all</a>
                <?php endif; ?>
            </div>
            <div class="filter-panel-body">
                <?php include ROOT_PATH . '/app/Views/public/_filter_form.php'; ?>
            </div>
        </div>
    </div>

    <!-- ── Results column ─────────────────────────────────── -->
    <div>

        <!-- Active filter chips -->
        <?php
        $chipMap = [
            'q'             => fn($v) => 'Search: ' . $v,
            'price_min'     => fn($v) => 'Min GH₵' . number_format($v),
            'price_max'     => fn($v) => 'Max GH₵' . number_format($v),
            'bedrooms'      => fn($v) => $v === '0' ? 'Room' : $v . ' bed' . ($v === '1' ? '' : 's'),
            'self_contained'=> fn($v) => 'Self-Contained',
            'parking'       => fn($v) => 'Parking',
            'water'         => fn($v) => 'Water',
            'vehicle_access'=> fn($v) => 'Car Access',
            'gated'         => fn($v) => 'Gated',
            'walk_max'      => fn($v) => 'Walk ≤' . $v . 'min',
            'electricity'   => fn($v) => ucwords(str_replace('_',' ',$v)),
        ];
        $chips = [];
        foreach ($chipMap as $key => $label) {
            if (!empty($filters[$key])) $chips[$key] = $label($filters[$key]);
        }
        if (!empty($chips)): ?>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px">
            <?php foreach ($chips as $key => $chip): ?>
            <span class="active-filter">
                <?= Security::e($chip) ?>
                <a href="<?= Url::base('properties?' . http_build_query(array_filter(array_merge($filters, [$key => null]), fn($v) => $v !== null && $v !== '' && $v !== 'verified'))) ?>"
                   style="color:rgba(255,255,255,.7);text-decoration:none;font-size:14px;line-height:1">&times;</a>
            </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Results header -->
        <div class="results-header">
            <div class="results-count">
                <?= number_format($total) ?>
                <span style="color:#5a7a6e;font-weight:400"> <?= $total === 1 ? 'property' : 'properties' ?> found</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:12px;color:#5a7a6e;font-weight:500">Sort:</span>
                <select class="form-select" style="width:auto;font-size:13px;color:#051F20" onchange="window.location.href=this.value">
                    <option value="<?= Url::base('properties?' . http_build_query(array_filter(array_merge($filters,['sort'=>'newest']),fn($v)=>$v!==null&&$v!==''&&$v!=='verified'))) ?>">Newest</option>
                    <option value="<?= Url::base('properties?' . http_build_query(array_filter(array_merge($filters,['sort'=>'price_asc']),fn($v)=>$v!==null&&$v!==''&&$v!=='verified'))) ?>">Price: Low to high</option>
                    <option value="<?= Url::base('properties?' . http_build_query(array_filter(array_merge($filters,['sort'=>'price_desc']),fn($v)=>$v!==null&&$v!==''&&$v!=='verified'))) ?>">Price: High to low</option>
                </select>
            </div>
        </div>

        <!-- Property grid -->
        <?php if (empty($properties)): ?>
        <div style="text-align:center;padding:80px 32px;border:1.5px solid var(--border);border-radius:12px;background:#fff">
            <div style="font-size:28px;font-weight:800;letter-spacing:-.04em;color:#051F20;margin-bottom:8px">No properties found</div>
            <p style="font-size:15px;color:#5a7a6e;margin-bottom:28px">Try adjusting your filters or searching a different area.</p>
            <a href="<?= Url::base('properties') ?>" class="btn btn-primary">Clear all filters</a>
        </div>
        <?php else: ?>
        <div class="pcard-grid">
            <?php foreach ($properties as $p): ?>
            <?php include ROOT_PATH . '/app/Views/partials/property_card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php if ($pager->lastPage > 1): ?>
        <div style="margin-top:48px">
            <?= $pager->render() ?>
            <p style="text-align:center;font-size:13px;color:#5a7a6e;margin-top:12px"><?= $pager->info() ?></p>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<!-- ── Custom mobile filter drawer ───────────────────────── -->
<div class="ff-drawer-backdrop" id="ffBackdrop"
     onclick="document.getElementById('ffDrawer').classList.remove('open');document.body.style.overflow='';"></div>

<div class="ff-drawer" id="ffDrawer" role="dialog" aria-label="Filters">
    <!-- Header -->
    <div class="ff-drawer-head">
        <div class="ff-drawer-head-left">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="20" y2="12"/><line x1="12" y1="18" x2="20" y2="18"/></svg>
            <span>Filters</span>
            <?php if ($activeCount): ?>
            <span class="ff-drawer-badge"><?= $activeCount ?> active</span>
            <?php endif; ?>
        </div>
        <button class="ff-drawer-close" aria-label="Close filters"
                onclick="document.getElementById('ffDrawer').classList.remove('open');document.body.style.overflow=''">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <!-- Scrollable body -->
    <div class="ff-drawer-body">
        <?php include ROOT_PATH . '/app/Views/public/_filter_form.php'; ?>
    </div>
</div>

<script>
// Responsive: hide desktop filter + show mobile button on small screens
(function () {
    function check() {
        var desktop  = document.getElementById('desktopFilter');
        var mobileBtn = document.getElementById('mobileFilterBtn');
        var grid = desktop?.parentElement;
        if (window.innerWidth < 900) {
            if (desktop) desktop.style.display = 'none';
            if (mobileBtn) mobileBtn.style.display = 'inline-flex';
            if (grid) grid.style.gridTemplateColumns = '1fr';
        } else {
            if (desktop) desktop.style.display = 'block';
            if (mobileBtn) mobileBtn.style.display = 'none';
            if (grid) grid.style.gridTemplateColumns = '280px 1fr';
        }
    }
    check();
    window.addEventListener('resize', check);

    // Drawer open/close + backdrop
    var drawer   = document.getElementById('ffDrawer');
    var backdrop = document.getElementById('ffBackdrop');

    function openDrawer() {
        drawer.classList.add('open');
        backdrop.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer() {
        drawer.classList.remove('open');
        backdrop.style.display = 'none';
        document.body.style.overflow = '';
    }

    document.getElementById('mobileFilterBtn')?.addEventListener('click', openDrawer);
    backdrop?.addEventListener('click', closeDrawer);
    document.querySelectorAll('.ff-drawer-close').forEach(function (btn) {
        btn.addEventListener('click', closeDrawer);
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDrawer();
    });
})();
</script>
