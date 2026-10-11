<?php
/**
 * Filter form — shared between desktop sidebar and mobile drawer.
 * Expects: $filters, $types, $regions, $cities, $ceiltypes, $floortypes
 */
use App\Helpers\{Url, Security};
$f = $filters;

// Count active filters for badge
$activeFilters = array_filter($f, fn($v) => $v !== null && $v !== '' && $v !== [] && $v !== 'verified');
$activeCount   = count($activeFilters);
?>
<form method="GET" action="<?= Url::base('properties') ?>" id="filterForm">

    <!-- ── Search ─────────────────────────────────────────── -->
    <div class="ff-section">
        <label class="ff-label">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Keyword
        </label>
        <div class="ff-input-wrap">
            <input type="text" name="q" class="ff-input" placeholder="Area, street, description…"
                   value="<?= Security::e($f['q'] ?? '') ?>">
            <?php if (!empty($f['q'])): ?>
            <button type="button" class="ff-input-clear" onclick="this.previousElementSibling.value='';this.closest('form').submit()">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Location ───────────────────────────────────────── -->
    <div class="ff-section">
        <label class="ff-label">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Location
        </label>
        <div class="ff-stack">
            <select name="region_id" class="ff-select" id="filterRegion">
                <option value="">All Regions</option>
                <?php foreach ($regions as $r): ?>
                <option value="<?= $r['id'] ?>" <?= (string)($f['region_id'] ?? '') === (string)$r['id'] ? 'selected' : '' ?>>
                    <?= Security::e($r['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <select name="city_id" class="ff-select" id="filterCity">
                <option value="">All Cities</option>
                <?php foreach ($cities as $c): ?>
                <option value="<?= $c['id'] ?>" <?= (string)($f['city_id'] ?? '') === (string)$c['id'] ? 'selected' : '' ?>>
                    <?= Security::e($c['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- ── Property type ──────────────────────────────────── -->
    <div class="ff-section">
        <label class="ff-label">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Property Type
        </label>
        <select name="type_id" class="ff-select">
            <option value="">Any type</option>
            <?php foreach ($types as $t): ?>
            <option value="<?= $t['id'] ?>" <?= (string)($f['type_id'] ?? '') === (string)$t['id'] ? 'selected' : '' ?>>
                <?= Security::e($t['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- ── Price range ────────────────────────────────────── -->
    <div class="ff-section">
        <label class="ff-label">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Monthly Rent (GH₵)
        </label>
        <div class="ff-price-row">
            <div class="ff-price-field">
                <span class="ff-price-symbol">₵</span>
                <input type="number" name="price_min" class="ff-input ff-price-input" placeholder="Min"
                       value="<?= Security::e($f['price_min'] ?? '') ?>">
            </div>
            <span class="ff-price-dash">—</span>
            <div class="ff-price-field">
                <span class="ff-price-symbol">₵</span>
                <input type="number" name="price_max" class="ff-input ff-price-input" placeholder="Max"
                       value="<?= Security::e($f['price_max'] ?? '') ?>">
            </div>
        </div>
    </div>

    <!-- ── Bedrooms ────────────────────────────────────────── -->
    <div class="ff-section">
        <label class="ff-label">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M2 9V6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v3"/><path d="M2 11v9"/><path d="M22 11v9"/><rect x="6" y="11" width="4" height="5" rx="1"/><rect x="14" y="11" width="4" height="5" rx="1"/><path d="M2 20h20"/></svg>
            Bedrooms
        </label>
        <div class="ff-chips">
            <?php foreach (['' => 'Any', '0' => 'Room', '1' => '1', '2' => '2', '3' => '3', '4+' => '4+'] as $v => $l): ?>
            <label class="ff-chip <?= ($f['bedrooms'] ?? '') === $v ? 'ff-chip--active' : '' ?>">
                <input type="radio" name="bedrooms" value="<?= $v ?>"
                       <?= ($f['bedrooms'] ?? '') === $v ? 'checked' : '' ?>
                       style="display:none" onchange="this.closest('form').submit()">
                <?= $l ?>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Features ────────────────────────────────────────── -->
    <div class="ff-section">
        <label class="ff-label">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            Features
        </label>
        <div class="ff-toggles">
            <?php
            $toggles = [
                'self_contained' => ['label' => 'Self-Contained'],
                'parking'        => ['label' => 'Parking'],
                'water'          => ['label' => 'Water Available'],
                'vehicle_access' => ['label' => 'Vehicle Access'],
                'gated'          => ['label' => 'Gated Compound'],
            ];
            foreach ($toggles as $name => $meta):
                $checked = !empty($f[$name]);
            ?>
            <label class="ff-toggle-row <?= $checked ? 'ff-toggle-row--on' : '' ?>">
                <div class="ff-toggle-left">
                    <span class="ff-toggle-label"><?= $meta['label'] ?></span>
                </div>
                <div class="ff-toggle-switch <?= $checked ? 'ff-toggle-switch--on' : '' ?>">
                    <input type="checkbox" name="<?= $name ?>" value="1"
                           <?= $checked ? 'checked' : '' ?>
                           style="display:none"
                           onchange="this.closest('.ff-toggle-row').classList.toggle('ff-toggle-row--on',this.checked);this.closest('.ff-toggle-switch').classList.toggle('ff-toggle-switch--on',this.checked);this.closest('form').submit()">
                    <span class="ff-toggle-knob"></span>
                </div>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Walking time ────────────────────────────────────── -->
    <div class="ff-section">
        <label class="ff-label">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Walk to Main Road
        </label>
        <div class="ff-chips">
            <?php foreach (['' => 'Any', '5' => '≤5 min', '10' => '≤10 min', '15' => '≤15 min', '20' => '≤20 min'] as $v => $l): ?>
            <label class="ff-chip <?= (string)($f['walk_max'] ?? '') === $v ? 'ff-chip--active' : '' ?>">
                <input type="radio" name="walk_max" value="<?= $v ?>"
                       <?= (string)($f['walk_max'] ?? '') === $v ? 'checked' : '' ?>
                       style="display:none" onchange="this.closest('form').submit()">
                <?= $l ?>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Advanced ─────────────────────────────────────────── -->
    <details class="ff-advanced" <?= (!empty($f['electricity']) || !empty($f['ceiling_type']) || !empty($f['flooring_type']) || !empty($f['building_level'])) ? 'open' : '' ?>>
        <summary class="ff-advanced-toggle">
            <span>More filters</span>
            <svg class="ff-advanced-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
        </summary>
        <div class="ff-advanced-body">

            <div class="ff-section ff-section--sm">
                <label class="ff-label">Electricity</label>
                <select name="electricity" class="ff-select">
                    <option value="">Any</option>
                    <?php foreach (['prepaid' => 'Prepaid', 'self_meter' => 'Self Meter', 'shared_meter' => 'Shared Meter', 'ecg' => 'ECG'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= ($f['electricity'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="ff-section ff-section--sm">
                <label class="ff-label">Ceiling Type</label>
                <select name="ceiling_type" class="ff-select">
                    <option value="">Any</option>
                    <?php foreach ($ceiltypes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (string)($f['ceiling_type'] ?? '') === (string)$c['id'] ? 'selected' : '' ?>><?= Security::e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="ff-section ff-section--sm">
                <label class="ff-label">Flooring</label>
                <select name="flooring_type" class="ff-select">
                    <option value="">Any</option>
                    <?php foreach ($floortypes as $ft): ?>
                    <option value="<?= $ft['id'] ?>" <?= (string)($f['flooring_type'] ?? '') === (string)$ft['id'] ? 'selected' : '' ?>><?= Security::e($ft['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="ff-section ff-section--sm" style="margin-bottom:0">
                <label class="ff-label">Floor Level</label>
                <div class="ff-chips">
                    <?php foreach (['' => 'Any', 'ground' => 'Ground', 'first' => '1st', 'second' => '2nd', 'third' => '3rd'] as $v => $l): ?>
                    <label class="ff-chip <?= ($f['building_level'] ?? '') === $v ? 'ff-chip--active' : '' ?>">
                        <input type="radio" name="building_level" value="<?= $v ?>"
                               <?= ($f['building_level'] ?? '') === $v ? 'checked' : '' ?>
                               style="display:none" onchange="this.closest('form').submit()">
                        <?= $l ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </details>

    <!-- ── Actions ─────────────────────────────────────────── -->
    <div class="ff-actions">
        <button type="submit" class="ff-btn-apply">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Apply Filters
            <?php if ($activeCount): ?><span class="ff-btn-badge"><?= $activeCount ?></span><?php endif; ?>
        </button>
        <?php if ($activeCount): ?>
        <a href="<?= Url::base('properties') ?>" class="ff-btn-clear">Clear all</a>
        <?php endif; ?>
    </div>

</form>

<script>
(function () {
    var regionSel = document.getElementById('filterRegion');
    var citySel   = document.getElementById('filterCity');
    if (!regionSel || !citySel) return;
    var base = (window.APP_BASE_URL || '').replace(/\/+$/, '');

    regionSel.addEventListener('change', function () {
        var rid = this.value;
        citySel.innerHTML = '<option value="">Loading…</option>';
        if (!rid) { citySel.innerHTML = '<option value="">All Cities</option>'; return; }
        fetch(base + '/api/cities/' + rid)
            .then(function (r) { return r.json(); })
            .then(function (d) {
                citySel.innerHTML = '<option value="">All Cities</option>';
                (d.data || []).forEach(function (c) {
                    citySel.innerHTML += '<option value="' + c.id + '">' + c.name + '</option>';
                });
            })
            .catch(function () { citySel.innerHTML = '<option value="">All Cities</option>'; });
    });
})();
</script>
