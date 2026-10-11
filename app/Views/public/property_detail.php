<?php
use App\Helpers\{Url, Security, Str};
$isLoggedIn = !empty($_SESSION['user_id']);
$isRenter   = $isLoggedIn && ($_SESSION['user_role'] ?? '') === 'renter';
$images     = $property['images']        ?? [];
$spaces     = $property['spaces']        ?? [];
$amenities  = $property['amenities']     ?? [];
$acc        = $property['accessibility'] ?? [];
$sec        = $property['security']      ?? [];
$videos     = $property['videos']        ?? [];
$location   = $property['location']      ?? [];
$reviews    = $reviews                   ?? [];

$primaryImg = null;
foreach ($images as $img) { if ($img['is_primary']) { $primaryImg = $img; break; } }
if (!$primaryImg && !empty($images)) $primaryImg = $images[0];

$locationParts = array_filter([
    $location['street'] ?? null, $location['community'] ?? null,
    $property['area_name'] ?? null, $property['city_name'] ?? null, $property['region_name'] ?? null,
]);
$locationStr = implode(', ', $locationParts);

$elecLabels  = ['self_meter'=>'Self Meter','shared_meter'=>'Shared Meter','prepaid'=>'Prepaid','postpaid'=>'Postpaid','ecg'=>'ECG','other'=>'Other'];
$waterLabels = ['ghana_water'=>'Ghana Water Co.','borehole'=>'Borehole','tank'=>'Tank','well'=>'Well','other'=>'Other'];

// structured data (JSON-LD) — helps Google display price/location/rating in search results
$ldImages = [];
foreach (array_slice($images, 0, 5) as $img) {
    if (!empty($img['path'])) $ldImages[] = Url::image($img['path']);
}
$ldJson = [
    '@context'        => 'https://schema.org',
    '@type'           => 'Accommodation',
    'name'            => $property['title'],
    'description'     => Str::truncate(strip_tags($property['description'] ?? ''), 300),
    'url'             => Url::current(),
    'image'           => $ldImages ?: null,
    'datePublished'   => $property['created_at'] ?? null,
    'numberOfRooms'   => (int) ($property['num_bedrooms'] ?? 0) ?: null,
    'numberOfBathroomsTotal' => (int) ($property['num_bathrooms'] ?? 0) ?: null,
    'petsAllowed'     => ($property['pet_policy'] ?? '') === 'allowed',
];
if (!empty($property['rent_monthly'])) {
    $ldJson['offers'] = [
        '@type'         => 'Offer',
        'price'         => (float) $property['rent_monthly'],
        'priceCurrency' => $property['currency'] ?? 'GHS',
        'availability'  => $property['status'] === 'verified' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url'           => Url::current(),
    ];
}
if ($locationStr) {
    $ldJson['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $locationStr];
}
if ($property['location_public'] && !empty($location['latitude']) && !empty($location['longitude'])) {
    $ldJson['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) $location['latitude'], 'longitude' => (float) $location['longitude']];
}
if (($avgRatings['total'] ?? 0) > 0 && ($avgRatings['avg_overall'] ?? null) !== null) {
    $ldJson['aggregateRating'] = [
        '@type'       => 'AggregateRating',
        'ratingValue' => $avgRatings['avg_overall'],
        'reviewCount' => $avgRatings['total'],
    ];
}
$ldJson = array_filter($ldJson, fn($v) => $v !== null && $v !== []);
?>

<!-- Breadcrumb -->
<div style="background:#fff;border-bottom:1.5px solid var(--border);padding:12px 0">
    <div style="max-width:1400px;margin:0 auto;padding:0 32px">
        <nav style="font-size:12px;color:#5a7a6e;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
            <a href="<?= Url::base() ?>" style="color:#5a7a6e;font-weight:500">Home</a>
            <span style="color:var(--border-mid)">/</span>
            <a href="<?= Url::base('properties') ?>" style="color:#5a7a6e;font-weight:500">Properties</a>
            <?php if (!empty($property['city_name'])): ?>
            <span style="color:var(--border-mid)">/</span>
            <a href="<?= Url::base('properties?city_id=' . ($location['city_id'] ?? '')) ?>" style="color:#5a7a6e;font-weight:500"><?= Security::e($property['city_name']) ?></a>
            <?php endif; ?>
            <span style="color:var(--border-mid)">/</span>
            <span style="color:#051F20;font-weight:600;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= Security::e($property['title']) ?></span>
        </nav>
    </div>
</div>

<!-- Main layout -->
<div style="max-width:1400px;margin:0 auto;padding:36px 32px 96px;display:grid;grid-template-columns:1fr 360px;gap:40px;align-items:start" class="prop-detail-layout">

    <!-- ══ LEFT COLUMN ══════════════════════════════════════ -->
    <div>

        <!-- Gallery -->
        <?php if (!empty($images)): ?>
        <div style="margin-bottom:32px">
            <div class="detail-gallery-main" id="galleryMain" onclick="openLightbox(0)" style="cursor:pointer">
                <img src="<?= Url::image($primaryImg['path']) ?>" id="galleryMainImg" alt="<?= Security::e($property['title']) ?>">
            </div>
            <?php if (count($images) > 1): ?>
            <div class="detail-gallery-thumbs" style="margin-top:8px">
                <?php foreach (array_slice($images, 0, 8) as $i => $img): ?>
                <div class="detail-gallery-thumb <?= $i === 0 ? 'active' : '' ?>"
                     onclick="switchImage(this,'<?= Url::image($img['path']) ?>')">
                    <img src="<?= Url::image($img['thumbnail'] ?: $img['path']) ?>" alt="<?= Security::e($img['category']) ?>" loading="lazy">
                </div>
                <?php endforeach; ?>
                <?php if (count($images) > 8): ?>
                <div style="border:1.5px solid var(--border);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;height:88px;cursor:pointer;font-size:13px;font-weight:700;color:#235347;background:var(--off-white)" onclick="openLightbox(8)">
                    +<?= count($images) - 8 ?> more
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Title & badges -->
        <div style="margin-bottom:24px">
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px">
                <?php if ($property['verified']): ?><span class="prop-badge verified">Agency Verified</span><?php endif; ?>
                <?php if (!empty($property['is_self_contained'])): ?><span class="prop-badge">Self-Contained</span><?php endif; ?>
                <span class="prop-badge" style="background:#DAF1DE;color:#235347"><?= Security::e($property['type_name']) ?></span>
                <?php if ($property['is_featured']): ?><span class="prop-badge" style="background:#DAF1DE;color:#235347">Featured</span><?php endif; ?>
            </div>
            <h1 style="font-size:clamp(1.6rem,3vw,2.1rem);font-weight:800;letter-spacing:-.035em;color:#051F20;margin-bottom:8px;line-height:1.15"><?= Security::e($property['title']) ?></h1>
            <?php if ($locationStr): ?>
            <p style="font-size:14px;color:#5a7a6e;margin:0"><?= Security::e($locationStr) ?></p>
            <?php endif; ?>
        </div>

        <!-- Spec bar -->
        <div style="display:flex;flex-wrap:wrap;gap:8px;padding:18px 20px;border:1.5px solid var(--border);border-radius:var(--radius-lg);margin-bottom:28px;background:var(--off-white)">
            <?php
            $specBar = array_filter([
                $property['num_bedrooms'] . ' Bedroom' . ($property['num_bedrooms'] != 1 ? 's' : ''),
                $property['num_bathrooms'] . ' Bathroom' . ($property['num_bathrooms'] != 1 ? 's' : ''),
                $property['num_toilets'] . ' Toilet' . ($property['num_toilets'] != 1 ? 's' : ''),
                ucfirst(str_replace('_',' ',$property['furnished'])),
                ucfirst($property['building_level']) . ' Floor',
                $property['has_parking'] ? 'Parking' : null,
                $property['water_available'] ? 'Water Available' : null,
                $property['elevator_available'] ? 'Elevator' : null,
                $property['has_balcony'] ? 'Balcony' : null,
            ]);
            foreach ($specBar as $s): ?>
            <span class="prop-spec" style="font-size:12px;color:#235347"><?= Security::e($s) ?></span>
            <?php endforeach; ?>
        </div>

        <!-- Description -->
        <div class="detail-section">
            <h3>About this property</h3>
            <div style="font-size:15px;line-height:1.8;color:#051F20;white-space:pre-line"><?= Security::e($property['description']) ?></div>
        </div>

        <!-- Condition report (admin-assessed) -->
        <?php if (!empty($conditionReport)):
            $condMeta = \App\Models\PropertyConditionModel::LEVEL_META;
            $condAspects = \App\Models\PropertyConditionModel::ASPECTS;
            $cm = $condMeta[$conditionReport['overall_condition']] ?? $condMeta['good'];
            $condPhotos = \App\Models\PropertyConditionModel::photos($conditionReport['photos'] ?? null);
        ?>
        <div class="detail-section" id="condition">
            <h3>Condition Report</h3>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px">
                <span style="display:inline-flex;align-items:center;gap:8px;background:<?= $cm['bg'] ?>;color:<?= $cm['color'] ?>;border:1.5px solid <?= $cm['color'] ?>33;border-radius:20px;padding:7px 16px;font-size:14px;font-weight:700">
                    Overall: <?= Security::e($cm['label']) ?>
                </span>
                <?php foreach ($condAspects as $key => $label): if (empty($conditionReport[$key])) continue; $m = $condMeta[$conditionReport[$key]]; ?>
                <span style="display:inline-block;background:<?= $m['bg'] ?>;color:<?= $m['color'] ?>;border:1px solid <?= $m['color'] ?>33;border-radius:16px;padding:5px 12px;font-size:12px;font-weight:600">
                    <?= Security::e($label) ?>: <?= Security::e($m['label']) ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($conditionReport['summary'])): ?>
            <p style="font-size:14px;line-height:1.7;color:#051F20;margin-bottom:10px"><?= nl2br(Security::e($conditionReport['summary'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($conditionReport['issues'])): ?>
            <div style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:9px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:#991b1b;line-height:1.6">
                <strong>Known issues:</strong> <?= nl2br(Security::e($conditionReport['issues'])) ?>
            </div>
            <?php endif; ?>
            <?php if ($condPhotos): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:8px;margin-bottom:10px">
                <?php foreach ($condPhotos as $p): ?>
                <img src="<?= Url::image($p['path']) ?>" alt="<?= Security::e($p['caption'] ?? 'Condition photo') ?>" loading="lazy"
                     style="width:100%;height:84px;object-fit:cover;border-radius:8px;border:1.5px solid var(--border)">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <p style="font-size:12px;color:#5a7a6e;margin:0">
                Assessed<?= $conditionReport['assessment_date'] ? ' on ' . date('d M Y', strtotime($conditionReport['assessment_date'])) : '' ?>
                during our physical inspection. See it before you visit — no surprises.
            </p>
        </div>
        <?php endif; ?>

        <!-- Road Accessibility -->
        <?php if (!empty($acc)): ?>
        <div class="detail-section">
            <h3>Road Accessibility</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-bottom:14px">
                <?php
                $accBoxes = [
                    ['label'=>'Walk to road', 'value'=>($acc['walking_time_minutes']??'—').(!empty($acc['walking_time_minutes'])?' min':''), 'good'=>isset($acc['walking_time_minutes'])&&(int)$acc['walking_time_minutes']<=10],
                    ['label'=>'Vehicle Access','value'=>!empty($acc['vehicle_accessible'])?'Yes':'No', 'good'=>!empty($acc['vehicle_accessible'])],
                    ['label'=>'Main Road',    'value'=>($acc['distance_main_road_km']??'—').(!empty($acc['distance_main_road_km'])?' km':''), 'good'=>false],
                    ['label'=>'Road Type',    'value'=>ucfirst($acc['road_condition']??'—'), 'good'=>false],
                ];
                foreach ($accBoxes as $ab): ?>
                <div class="access-box <?= $ab['good'] ? 'good' : '' ?>">
                    <div class="access-box-val" style="color:<?= $ab['good'] ? '#fff' : '#051F20' ?>"><?= Security::e($ab['value']) ?></div>
                    <div class="access-box-label" style="color:<?= $ab['good'] ? 'rgba(218,241,222,.65)' : '#5a7a6e' ?>"><?= $ab['label'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php
            $accExtras = array_filter(['taxi_accessible'=>'Taxi Access','motorbike_accessible'=>'Motorbike','public_transport_nearby'=>'Public Transport'], fn($k)=>!empty($acc[$k]), ARRAY_FILTER_USE_KEY);
            if ($accExtras): ?>
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                <?php foreach ($accExtras as $l): ?><span class="prop-spec" style="color:#235347"><?= $l ?></span><?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($acc['accessibility_notes'])): ?>
            <p style="font-size:14px;color:#5a7a6e;margin-top:14px;line-height:1.7"><?= Security::e($acc['accessibility_notes']) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Room Dimensions -->
        <?php if (!empty($spaces)): ?>
        <div class="detail-section">
            <h3>Room Dimensions</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(148px,1fr));gap:10px">
                <?php foreach ($spaces as $s): ?>
                <div class="dimension-block">
                    <div class="dimension-block-name" style="color:#5a7a6e"><?= Security::e($s['space_name']) ?></div>
                    <div class="dimension-block-size" style="color:#235347"><?= $s['length'] ?>&times;<?= $s['width'] ?>&times;<?= $s['height'] ?><?= Security::e($s['unit']??'m') ?></div>
                    <?php if (!empty($s['area_sqm'])): ?><div class="dimension-block-area" style="color:#5a7a6e"><?= number_format((float)$s['area_sqm'],1) ?> m&sup2;</div><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Full Details -->
        <div class="detail-section">
            <h3>Full Details</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:10px">
                <?php
                $details = [
                    'Ceiling'        => $property['ceiling_name']   ?? '—',
                    'Flooring'       => $property['flooring_name']  ?? '—',
                    'Toilet'         => ucwords(str_replace('_',' ',$property['toilet_location']   ?? '—')),
                    'Bathroom'       => ucwords(str_replace('_',' ',$property['bathroom_location'] ?? '—')),
                    'Kitchen Size'   => ucfirst($property['kitchen_size']  ?? '—'),
                    'Kitchen Type'   => ucfirst($property['kitchen_type']  ?? '—'),
                    'Parking'        => $property['has_parking'] ? ($property['parking_spaces'].' space'.($property['parking_spaces']!=1?'s':'')) : 'None',
                    'Pet Policy'     => ucwords(str_replace('_',' ',$property['pet_policy'] ?? '—')),
                    'Porch'          => $property['has_porch'] ? 'Yes' : 'No',
                    'Wardrobe'       => $property['has_wardrobe'] ? ($property['wardrobe_builtin']?'Built-in':'Standalone') : 'No',
                    'Max Occupants'  => $property['max_occupants'] ?? 'Not specified',
                    'Min Lease'      => ($property['min_lease_months']??1).' month(s)',
                    'Available From' => $property['available_from'] ? date('d M Y',strtotime($property['available_from'])) : 'Now',
                ];
                foreach ($details as $label => $val): ?>
                <div class="spec-box">
                    <div class="spec-box-label" style="color:#5a7a6e"><?= $label ?></div>
                    <div class="spec-box-value" style="color:#051F20"><?= Security::e((string)$val) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Utilities -->
        <div class="detail-section">
            <h3>Utilities</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
                <div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#5a7a6e;margin-bottom:10px">Electricity</div>
                    <?php $elecTypes = array_filter(explode(',', $property['electricity_type']??'')); ?>
                    <?php if ($elecTypes): ?>
                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                        <?php foreach ($elecTypes as $et): ?><span class="prop-spec" style="color:#235347"><?= Security::e($elecLabels[trim($et)] ?? ucfirst(trim($et))) ?></span><?php endforeach; ?>
                    </div>
                    <?php else: ?><span style="font-size:13px;color:#5a7a6e">Not specified</span><?php endif; ?>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#5a7a6e;margin-bottom:10px">Water</div>
                    <div class="info-row" style="padding:8px 0"><span class="info-label" style="color:#5a7a6e">Available</span><span style="font-size:13px;font-weight:700;color:#051F20"><?= $property['water_available'] ? 'Yes' : 'No' ?></span></div>
                    <div class="info-row" style="padding:8px 0"><span class="info-label" style="color:#5a7a6e">Type</span><span style="font-size:13px;font-weight:700;color:#051F20"><?= ucfirst($property['water_type']??'—') ?></span></div>
                    <?php if ($property['water_frequency']): ?>
                    <div class="info-row" style="padding:8px 0"><span class="info-label" style="color:#5a7a6e">Availability</span><span style="font-size:13px;font-weight:700;color:#051F20"><?= Security::e($property['water_frequency']) ?></span></div>
                    <?php endif; ?>
                    <?php $waterSources = array_filter(explode(',', $property['water_source']??'')); ?>
                    <?php if ($waterSources): ?>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px">
                        <?php foreach ($waterSources as $ws): ?><span class="prop-spec" style="color:#235347"><?= Security::e($waterLabels[trim($ws)]??ucfirst(trim($ws))) ?></span><?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Security -->
        <?php if (!empty($sec)): ?>
        <div class="detail-section">
            <h3>Security &amp; Environment</h3>
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                <?php foreach (['gated'=>'Gated','security_guard'=>'Security Guard','cctv'=>'CCTV','street_lighting'=>'Street Lighting','security_gate'=>'Security Gate','fenced'=>'Fenced','intercom'=>'Intercom','alarm_system'=>'Alarm System'] as $k => $l):
                    if (!empty($sec[$k])): ?><span class="prop-spec highlight" style="color:#fff"><?= $l ?></span><?php endif;
                endforeach; ?>
            </div>
            <?php if (!empty($sec['security_notes'])): ?><p style="font-size:14px;color:#5a7a6e;margin-top:14px"><?= Security::e($sec['security_notes']) ?></p><?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Amenities -->
        <?php if (!empty($amenities)): ?>
        <div class="detail-section">
            <h3>Amenities</h3>
            <?php $amenByCat = []; foreach ($amenities as $a) $amenByCat[$a['category']][] = $a; foreach ($amenByCat as $cat => $items): ?>
            <div style="margin-bottom:14px">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#5a7a6e;margin-bottom:8px"><?= ucfirst($cat) ?></div>
                <div style="display:flex;flex-wrap:wrap;gap:8px">
                    <?php foreach ($items as $a): ?><span class="prop-spec" style="color:#235347"><?= Security::e($a['name']) ?></span><?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Video -->
        <?php if (!empty($videos)): ?>
        <div class="detail-section">
            <h3>Property Video</h3>
            <?php foreach ($videos as $vid):
                if ($vid['type']==='upload' && !empty($vid['path'])): ?>
                <video style="width:100%;border-radius:var(--radius-lg);max-height:400px;background:#000;display:block" controls>
                    <source src="<?= Url::base($vid['path']) ?>" type="video/mp4">
                </video>
                <?php elseif (!empty($vid['external_url'])):
                    preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $vid['external_url'], $ytM);
                    if (!empty($ytM[1])): ?>
                <div style="aspect-ratio:16/9;border-radius:var(--radius-lg);overflow:hidden">
                    <iframe src="https://www.youtube.com/embed/<?= Security::e($ytM[1]) ?>" style="width:100%;height:100%;border:none;display:block" allowfullscreen></iframe>
                </div>
                <?php endif; endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Reviews -->
        <?php if (!empty($reviews) || (isset($avgRatings) && $avgRatings['total'] > 0)): ?>
        <div class="detail-section">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
                <h3 style="margin-bottom:0">Reviews</h3>
                <?php if (!empty($avgRatings) && $avgRatings['total'] > 0): ?>
                <div style="display:flex;align-items:baseline;gap:6px">
                    <span style="font-size:28px;font-weight:800;letter-spacing:-.04em;color:#235347"><?= number_format((float)$avgRatings['avg_overall'],1) ?></span>
                    <span style="font-size:13px;color:#5a7a6e">/ 5 &nbsp;(<?= $avgRatings['total'] ?> review<?= $avgRatings['total']!=1?'s':'' ?>)</span>
                </div>
                <?php endif; ?>
            </div>
            <?php foreach ($reviews as $rev): ?>
            <div style="padding:20px 0;border-bottom:1px solid var(--border)">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:10px">
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:36px;height:36px;border-radius:50%;background:#235347;color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0"><?= strtoupper(substr($rev['reviewer_name']??'?',0,1)) ?></div>
                        <div>
                            <div style="font-size:14px;font-weight:700;color:#051F20"><?= Security::e($rev['reviewer_name']??'Anonymous') ?></div>
                            <?php if (!empty($rev['title'])): ?><div style="font-size:13px;color:#5a7a6e"><?= Security::e($rev['title']) ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:13px;font-weight:700;color:#235347"><?= (int)$rev['rating_overall'] ?>/5</div>
                        <div style="font-size:12px;color:#5a7a6e"><?= date('d M Y',strtotime($rev['created_at'])) ?></div>
                    </div>
                </div>
                <p style="font-size:14px;line-height:1.7;color:#051F20;margin:0"><?= Security::e($rev['comment']??'') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Similar Properties -->
        <?php if (!empty($similar)): ?>
        <div class="detail-section" style="border-bottom:none;padding-bottom:0">
            <h3>Similar Properties</h3>
            <div class="pcard-grid">
                <?php foreach ($similar as $p): ?>
                <?php include ROOT_PATH . '/app/Views/partials/property_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /left column -->

    <!-- ══ RIGHT COLUMN — Sticky action panel ══════════════ -->
    <div style="position:sticky;top:calc(var(--nav-h) + 24px)">

        <!-- Verified pill -->
        <?php if ($property['verified']): ?>
        <div class="verified-pill" style="margin-bottom:18px">
            Agency Verified
            <?php if ($property['verification_ref']): ?>
            &nbsp;&middot;&nbsp;<span style="font-weight:400;opacity:.75"><?= Security::e($property['verification_ref']) ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Price -->
        <div style="font-size:32px;font-weight:800;color:#235347;letter-spacing:-.04em;line-height:1;margin-bottom:4px"><?= Str::currency((float)$property['rent_monthly']) ?></div>
        <div style="font-size:13px;color:#5a7a6e;margin-bottom:22px">per month</div>

        <!-- Breakdown -->
        <div style="margin-bottom:22px">
            <?php if ($property['rent_annual']): ?>
            <div class="info-row"><span class="info-label" style="color:#5a7a6e">Annual Rent</span><span style="font-size:13px;font-weight:700;color:#051F20"><?= Str::currency((float)$property['rent_annual']) ?></span></div>
            <?php endif; ?>
            <?php if ($property['security_deposit']): ?>
            <div class="info-row"><span class="info-label" style="color:#5a7a6e">Security Deposit</span><span style="font-size:13px;font-weight:700;color:#051F20"><?= Str::currency((float)$property['security_deposit']) ?></span></div>
            <?php endif; ?>
            <?php if ($property['agency_fee']): ?>
            <div class="info-row"><span class="info-label" style="color:#5a7a6e">Agency Fee</span><span style="font-size:13px;font-weight:700;color:#051F20"><?= Str::currency((float)$property['agency_fee']) ?></span></div>
            <?php endif; ?>
            <div class="info-row"><span class="info-label" style="color:#5a7a6e">Available From</span><span style="font-size:13px;font-weight:700;color:#051F20"><?= $property['available_from'] ? date('d M Y',strtotime($property['available_from'])) : 'Now' ?></span></div>
            <div class="info-row"><span class="info-label" style="color:#5a7a6e">Reference</span><span class="code"><?= Security::e($property['reference']) ?></span></div>
        </div>

        <!-- Viewing fee -->
        <div style="border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:16px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;background:var(--off-white)">
            <div>
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#5a7a6e;margin-bottom:2px">Viewing Fee</div>
                <div style="font-size:22px;font-weight:800;color:#235347;letter-spacing:-.03em"><?= Str::currency($viewingFee) ?></div>
            </div>
            <div style="font-size:12px;color:#5a7a6e;max-width:100px;text-align:right;line-height:1.5">Pay to confirm a visit</div>
        </div>

        <!-- Landlord contact (pay to unlock) -->
        <div style="border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:16px;margin-bottom:20px;background:#fff">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#5a7a6e;margin-bottom:10px">
                <i class="fa-solid fa-address-book me-1"></i>Landlord Contact
            </div>
            <?php if ($contactUnlocked): ?>
                <div style="font-size:13px;line-height:1.8;color:#051F20">
                    <div style="font-weight:700;margin-bottom:6px"><?= Security::e(trim(($property['landlord_first'] ?? '') . ' ' . ($property['landlord_last'] ?? ''))) ?></div>
                    <?php if (!empty($property['landlord_contact_phone'])): ?>
                    <div><i class="fa-solid fa-phone me-2" style="color:#235347;width:16px"></i><?= Security::e($property['landlord_contact_phone']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($property['landlord_contact_phone2'])): ?>
                    <div><i class="fa-solid fa-phone me-2" style="color:#235347;width:16px"></i><?= Security::e($property['landlord_contact_phone2']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($property['landlord_contact_email'])): ?>
                    <div><i class="fa-solid fa-envelope me-2" style="color:#235347;width:16px"></i><?= Security::e($property['landlord_contact_email']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($property['landlord_contact_address'])): ?>
                    <div><i class="fa-solid fa-location-dot me-2" style="color:#235347;width:16px"></i><?= Security::e($property['landlord_contact_address']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($property['landlord_contact_notes'])): ?>
                    <div style="margin-top:8px;padding-top:8px;border-top:1px dashed var(--border);color:#5a7a6e;font-size:12px"><i class="fa-solid fa-circle-info me-1"></i><?= Security::e($property['landlord_contact_notes']) ?></div>
                    <?php endif; ?>
                    <a href="<?= Url::base('renter/landlord-contact/view/' . $property['id']) ?>"
                       style="display:inline-block;margin-top:10px;font-size:12px;color:#235347;font-weight:600;text-decoration:underline">
                        View full details
                    </a>
                </div>
            <?php elseif ($isRenter): ?>
                <div style="font-size:13px;color:#5a7a6e;margin-bottom:12px;line-height:1.5">
                    Pay a one-time fee to reveal the landlord's phone, email and address for this property.
                </div>
                <form method="POST" action="<?= Url::base('renter/landlord-contact/unlock/' . $property['id']) ?>">
                    <?= Security::csrfField() ?>
                    <button type="submit" class="btn btn-primary btn-lg"
                            style="width:100%;text-align:center;display:block;background:#235347;border-color:#235347">
                        <i class="fa-solid fa-unlock-keyhole me-2"></i>Unlock Contact &middot; <?= Str::currency($unlockFee) ?>
                    </button>
                </form>
            <?php else: ?>
                <div style="font-size:13px;color:#5a7a6e;margin-bottom:12px;line-height:1.5">
                    Sign in as a renter to unlock the landlord's contact details for <?= Str::currency($unlockFee) ?>.
                </div>
                <a href="<?= Url::base('auth/login') ?>" class="btn btn-outline-primary"
                   style="width:100%;text-align:center;display:block;margin-bottom:8px">Sign in to unlock</a>
            <?php endif; ?>
        </div>

        <!-- CTA buttons -->
        <?php if ($isRenter): ?>
        <a href="<?= Url::base('renter/viewings/request/' . $property['id']) ?>"
           class="btn btn-primary btn-lg"
           style="width:100%;text-align:center;margin-bottom:10px;display:block">
            Request Viewing
        </a>
        <form method="POST" action="<?= Url::base('renter/saved/toggle') ?>" style="margin-bottom:10px">
            <?= \App\Helpers\Security::csrfField() ?>
            <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
            <button style="width:100%;<?= $isSaved ? 'background:var(--c-primary);color:#fff;border-color:var(--c-primary)' : 'background:#fff;color:#235347;border:1.5px solid var(--border)' ?>;border-radius:var(--radius-sm);padding:10px 20px;font-size:14px;font-weight:600;font-family:var(--font);cursor:pointer;transition:.15s">
                <?= $isSaved ? 'Saved' : 'Save Property' ?>
            </button>
        </form>
        <form method="POST" action="<?= Url::base($inCompare ? 'compare/remove' : 'compare/add') ?>" style="margin-bottom:10px">
            <?= \App\Helpers\Security::csrfField() ?>
            <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
            <button style="width:100%;background:#fff;color:#235347;border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:10px 20px;font-size:14px;font-weight:600;font-family:var(--font);cursor:pointer;transition:.15s">
                <?= $inCompare ? 'Remove from Compare' : 'Add to Compare' ?>
            </button>
        </form>
        <a href="<?= Url::base('renter/reports/create/' . $property['id']) ?>"
           style="display:block;text-align:center;font-size:13px;color:#5a7a6e;text-decoration:underline;margin-top:6px">
            Report this listing
        </a>

        <?php elseif ($isLoggedIn): ?>
        <div style="border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:16px;text-align:center;font-size:13px;color:#5a7a6e;background:var(--off-white)">
            Sign in as a renter to request a viewing.
        </div>

        <?php else: ?>
        <a href="<?= Url::base('auth/register') ?>" class="btn btn-primary btn-lg"
           style="width:100%;text-align:center;margin-bottom:10px;display:block">
            Register to Request Viewing
        </a>
        <a href="<?= Url::base('auth/login') ?>"
           style="width:100%;text-align:center;display:block;padding:10px 20px;background:#fff;color:#235347;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:14px;font-weight:600;margin-bottom:10px">
            Already have an account? Sign in
        </a>
        <?php endif; ?>

        <!-- Share -->
        <div style="border-top:1px solid var(--border);padding-top:18px;margin-top:18px">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#5a7a6e;margin-bottom:10px">Share</div>
            <div style="display:flex;gap:8px">
                <a href="https://wa.me/?text=<?= urlencode($property['title'] . ' — ' . \App\Helpers\Url::current()) ?>" target="_blank" rel="noopener"
                   class="btn btn-secondary btn-sm" style="flex:1;text-align:center;color:#235347">WhatsApp</a>
                <button class="btn btn-secondary btn-sm" style="flex:1;color:#235347"
                        onclick="navigator.clipboard.writeText('<?= Security::e(\App\Helpers\Url::current()) ?>').then(function(){this.textContent='Copied!'}.bind(this))">
                    Copy Link
                </button>
            </div>
        </div>

    </div><!-- /right column -->

</div><!-- /main layout -->

<!-- Lightbox -->
<?php if (!empty($images)): ?>
<div id="lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.95);z-index:9999;flex-direction:column;align-items:center;justify-content:center">
    <button onclick="closeLightbox()" style="position:absolute;top:20px;right:24px;background:none;border:none;color:#fff;font-size:28px;cursor:pointer;opacity:.7">&times;</button>
    <button id="lbPrev" onclick="lightboxNav(-1)" style="position:absolute;left:20px;top:50%;transform:translateY(-50%);background:none;border:1.5px solid rgba(255,255,255,.25);color:#fff;border-radius:50%;width:44px;height:44px;font-size:18px;cursor:pointer">&#8592;</button>
    <button id="lbNext" onclick="lightboxNav(1)"  style="position:absolute;right:20px;top:50%;transform:translateY(-50%);background:none;border:1.5px solid rgba(255,255,255,.25);color:#fff;border-radius:50%;width:44px;height:44px;font-size:18px;cursor:pointer">&#8594;</button>
    <img id="lbImg" src="" alt="" style="max-width:90vw;max-height:85vh;object-fit:contain;border-radius:4px">
    <div id="lbCaption" style="color:rgba(255,255,255,.55);font-size:13px;margin-top:12px;font-weight:500"></div>
</div>
<script>
(function () {
    var images = <?= json_encode(array_map(fn($img) => ['src'=>Url::image($img['path']),'caption'=>ucfirst($img['category']??'')], $images), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    var lbIdx = 0;
    window.openLightbox = function (idx) { lbIdx = idx; showLb(); document.getElementById('lightbox').style.display = 'flex'; document.body.style.overflow = 'hidden'; };
    window.closeLightbox = function () { document.getElementById('lightbox').style.display = 'none'; document.body.style.overflow = ''; };
    window.lightboxNav   = function (d)  { lbIdx = (lbIdx + d + images.length) % images.length; showLb(); };
    function showLb() { document.getElementById('lbImg').src = images[lbIdx].src; document.getElementById('lbCaption').textContent = images[lbIdx].caption + '  (' + (lbIdx + 1) + ' / ' + images.length + ')'; }
    document.addEventListener('keydown', function (e) { var lb = document.getElementById('lightbox'); if (lb.style.display !== 'flex') return; if (e.key==='Escape') closeLightbox(); if (e.key==='ArrowLeft') lightboxNav(-1); if (e.key==='ArrowRight') lightboxNav(1); });
})();
function switchImage(thumb, src) {
    document.getElementById('galleryMainImg').src = src;
    document.querySelectorAll('.detail-gallery-thumb').forEach(function(t){ t.classList.remove('active'); });
    thumb.classList.add('active');
}
</script>
<?php endif; ?>

<!-- Responsive: stack columns on small screen -->
<style>
@media (max-width: 900px) {
    .prop-detail-layout { grid-template-columns: 1fr !important; padding: 20px 16px 96px !important; }
}
</style>
<script>
(function () {
    var grid = document.querySelector('.prop-detail-layout');
    function check() { if (grid) grid.style.gridTemplateColumns = window.innerWidth < 900 ? '1fr' : '1fr 360px'; }
    check(); window.addEventListener('resize', check);
})();
</script>

<?php if (!empty($property['rent_monthly'])): ?>
<!-- Mobile sticky price + CTA bar (visible ≤767px via app.css) -->
<?php $primaryCta = $isRenter
    ? ['url' => Url::base('renter/viewings/request/' . $property['id']), 'label' => 'Request Viewing']
    : ($isLoggedIn
        ? null
        : ['url' => Url::base('auth/register'), 'label' => 'Register to Request Viewing']); ?>
<?php if ($primaryCta !== null): ?>
<div class="detail-sticky-bar">
    <div class="detail-sticky-price">
        <?= Str::currency((float)$property['rent_monthly']) ?><small>per month</small>
    </div>
    <a href="<?= $primaryCta['url'] ?>" class="btn btn-primary"><?= $primaryCta['label'] ?></a>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Structured data for search engines -->
<script type="application/ld+json"><?= json_encode($ldJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
