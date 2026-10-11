<?php
/**
 * Property Card
 * Expects: $p = row from PropertyModel::search()
 * Place inside a .pcard-grid or any CSS grid — no column wrapper needed.
 */
use App\Helpers\{Url, Security, Str};

$isLoggedIn = !empty($_SESSION['user_id']);
$isRenter   = $isLoggedIn && ($_SESSION['user_role'] ?? '') === 'renter';
$isSaved    = !empty($p['is_saved']);

$imgUrl   = $p['primary_thumb'] ?? $p['primary_image'] ?? null;
$locLine  = implode(', ', array_filter([$p['area_name'] ?? null, $p['city_name'] ?? null]));
$verified = !empty($p['verified']);
$featured = !empty($p['is_featured']);
$walkMin  = isset($p['walking_time_minutes']) ? (int)$p['walking_time_minutes'] : null;
$beds     = (int)($p['num_bedrooms']  ?? 0);
$baths    = (int)($p['num_bathrooms'] ?? 0);
$sc       = !empty($p['is_self_contained']);
$parking  = !empty($p['has_parking']);
$water    = !empty($p['water_available']);
?>

<article class="pcard">

    <!-- ── Photo ─────────────────────────────────────────── -->
    <a href="<?= Url::property($p) ?>" class="pcard__photo-link" tabindex="-1" aria-hidden="true">
        <div class="pcard__photo">
            <?php if ($imgUrl): ?>
            <img src="<?= Url::image($imgUrl) ?>"
                 alt="<?= Security::e($p['title']) ?>"
                 loading="lazy">
            <?php else: ?>
            <div class="pcard__no-photo">No photo</div>
            <?php endif; ?>

            <!-- Badges -->
            <div class="pcard__badges">
                <?php if ($verified): ?>
                <span class="pcard__badge pcard__badge--verified">&#10003;&nbsp;Verified</span>
                <?php endif; ?>
                <?php if ($featured): ?>
                <span class="pcard__badge pcard__badge--featured">Featured</span>
                <?php endif; ?>
            </div>

            <!-- Save heart -->
            <?php if ($isRenter): ?>
            <button class="pcard__heart btn-save-property <?= $isSaved ? 'is-saved' : '' ?>"
                    data-property-id="<?= (int)$p['id'] ?>"
                    type="button"
                    title="<?= $isSaved ? 'Remove from saved' : 'Save property' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="<?= $isSaved ? '#DAF1DE' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </button>
            <?php endif; ?>
        </div>
    </a>

    <!-- ── Body ──────────────────────────────────────────── -->
    <div class="pcard__body">

        <!-- Meta: location + type -->
        <div class="pcard__meta">
            <?php if ($locLine): ?>
            <span class="pcard__location"><?= Security::e($locLine) ?></span>
            <?php endif; ?>
            <?php if (!empty($p['type_name'])): ?>
            <span class="pcard__type"><?= Security::e($p['type_name']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Title -->
        <a href="<?= Url::property($p) ?>" class="pcard__title">
            <?= Security::e(Str::truncate($p['title'], 58)) ?>
        </a>

        <!-- Specs -->
        <div class="pcard__specs">
            <?php if ($beds > 0): ?>
            <span class="pcard__spec">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9V6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v3"/><path d="M2 11v9"/><path d="M22 11v9"/><rect x="6" y="11" width="4" height="5" rx="1"/><rect x="14" y="11" width="4" height="5" rx="1"/><path d="M2 20h20"/></svg>
                <?= $beds ?> bed<?= $beds > 1 ? 's' : '' ?>
            </span>
            <?php endif; ?>
            <?php if ($baths > 0): ?>
            <span class="pcard__spec">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><line x1="10" y1="5" x2="8" y2="7"/><path d="M2 12h20"/></svg>
                <?= $baths ?> bath<?= $baths > 1 ? 's' : '' ?>
            </span>
            <?php endif; ?>
            <?php if ($sc): ?>
            <span class="pcard__spec pcard__spec--accent">Self-Contained</span>
            <?php endif; ?>
            <?php if ($parking): ?>
            <span class="pcard__spec">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                Parking
            </span>
            <?php endif; ?>
            <?php if ($water): ?>
            <span class="pcard__spec">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                Water
            </span>
            <?php endif; ?>
        </div>

        <!-- Access info -->
        <?php if ($walkMin !== null || !empty($p['vehicle_accessible'])): ?>
        <div class="pcard__access">
            <?php if ($walkMin !== null): ?>
            <span class="pcard__access-dot <?= $walkMin <= 10 ? 'pcard__access-dot--green' : ($walkMin <= 20 ? 'pcard__access-dot--amber' : 'pcard__access-dot--red') ?>"></span>
            <span><?= $walkMin ?> min to main road</span>
            <?php endif; ?>
            <?php if (!empty($p['vehicle_accessible'])): ?>
            <?php if ($walkMin !== null): ?><span class="pcard__access-sep">·</span><?php endif; ?>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <span>Car access</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Spacer pushes price row to bottom -->
        <div style="flex:1;min-height:10px"></div>

        <div class="pcard__divider"></div>

        <!-- Price + CTA -->
        <div class="pcard__price-row">
            <div>
                <div class="pcard__price">
                    <?= Str::currency((float)$p['rent_monthly']) ?>
                </div>
                <div class="pcard__price-period">per month</div>
            </div>
            <a href="<?= Url::property($p) ?>" class="pcard__cta">
                View
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:4px"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>

    </div>
</article>
