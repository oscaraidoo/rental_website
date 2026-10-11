<?php use App\Helpers\{Url, Security, Str}; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#051F20;letter-spacing:-.03em;margin-bottom:4px">
            Compare Properties
            <?php if (!empty($properties)): ?>
            <span style="font-size:14px;font-weight:600;color:#8EB69B;margin-left:8px"><?= count($properties) ?> / 4</span>
            <?php endif; ?>
        </h1>
        <p style="font-size:13px;color:#5a7a6e;margin:0">Add up to 4 properties to compare side by side.</p>
    </div>
    <a href="<?= Url::base('properties') ?>" class="btn btn-primary btn-sm">+ Add More</a>
</div>

<?php if (empty($properties)): ?>
<div class="empty-state">
    <div class="empty-state-title">Nothing to compare yet</div>
    <div class="empty-state-sub">Browse properties and click "Add to Compare" to start comparing.</div>
    <a href="<?= Url::base('properties') ?>" class="btn btn-primary btn-sm" style="margin-top:4px">Browse Properties</a>
</div>

<?php else: ?>

<!-- ── Property cards at top ────────────────────────────────── -->
<div class="pcard-grid" style="margin-bottom:32px">
    <?php foreach ($properties as $p):
        $p['primary_image'] = $p['primary_thumb'] ?? null;
    ?>
    <div style="position:relative">
        <?php include ROOT_PATH . '/app/Views/partials/property_card.php'; ?>
        <!-- Remove from compare -->
        <form method="POST" action="<?= Url::base('compare/remove') ?>"
              style="position:absolute;top:10px;right:46px;z-index:10">
            <?= Security::csrfField() ?>
            <input type="hidden" name="property_id" value="<?= (int)$p['id'] ?>">
            <button style="width:32px;height:32px;border-radius:50%;
                           background:rgba(255,255,255,.92);border:1.5px solid #d6ede0;
                           font-size:15px;cursor:pointer;display:flex;align-items:center;
                           justify-content:center;color:#5a7a6e;backdrop-filter:blur(4px)"
                    data-confirm="Remove from compare?"
                    title="Remove">
                &times;
            </button>
        </form>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Comparison table ─────────────────────────────────────── -->
<div class="table-card">
    <div class="table-card-head">
        <span class="table-card-title">Side-by-side comparison</span>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f8fbf9">
                    <th style="padding:12px 18px;border-bottom:1.5px solid #d6ede0;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#5a7a6e;min-width:160px">Feature</th>
                    <?php foreach ($properties as $p): ?>
                    <th style="padding:12px 18px;border-bottom:1.5px solid #d6ede0;text-align:center;font-size:13px;font-weight:700;color:#051F20;min-width:190px">
                        <?= Security::e(Str::truncate($p['title'], 32)) ?>
                        <div style="font-size:11px;font-weight:500;color:#8EB69B;margin-top:2px"><?= Security::e($p['reference'] ?? '') ?></div>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <?php
            // Define rows
            $rows = [
                ['label'=>'Monthly Rent',   'key'=>'rent_monthly',         'fmt'=>'currency','best'=>'min'],
                ['label'=>'Type',           'key'=>'type_name',            'fmt'=>'text'],
                ['label'=>'City',           'key'=>'city_name',            'fmt'=>'text'],
                ['label'=>'Bedrooms',       'key'=>'num_bedrooms',         'fmt'=>'number'],
                ['label'=>'Bathrooms',      'key'=>'num_bathrooms',        'fmt'=>'number'],
                ['label'=>'Self-Contained', 'key'=>'is_self_contained',    'fmt'=>'bool'],
                ['label'=>'Furnished',      'key'=>'furnished',            'fmt'=>'text'],
                ['label'=>'Parking',        'key'=>'has_parking',          'fmt'=>'bool'],
                ['label'=>'Water',          'key'=>'water_available',      'fmt'=>'bool'],
                ['label'=>'Gated',          'key'=>'gated',                'fmt'=>'bool'],
                ['label'=>'Floor',          'key'=>'building_level',       'fmt'=>'text'],
                ['label'=>'Ceiling',        'key'=>'ceiling_name',         'fmt'=>'text'],
                ['label'=>'Flooring',       'key'=>'flooring_name',        'fmt'=>'text'],
                ['label'=>'Pet Policy',     'key'=>'pet_policy',           'fmt'=>'text'],
                ['label'=>'Walk to Road',   'key'=>'walking_time_minutes', 'fmt'=>'walk',   'best'=>'min'],
                ['label'=>'Car Access',     'key'=>'vehicle_accessible',   'fmt'=>'bool'],
                ['label'=>'Security Deposit','key'=>'security_deposit',    'fmt'=>'currency'],
                ['label'=>'Available From', 'key'=>'available_from',       'fmt'=>'date'],
                ['label'=>'Verified',       'key'=>'verified',             'fmt'=>'bool'],
            ];

            foreach ($rows as $idx => $row):
                // Compute best value
                $values  = array_column($properties, $row['key']);
                $bestVal = null;
                if (!empty($row['best'])) {
                    $nums = array_filter($values, fn($v) => is_numeric($v) && $v !== null);
                    if ($nums) $bestVal = $row['best'] === 'min' ? min($nums) : max($nums);
                }
                $rowBg = $idx % 2 === 0 ? '#fff' : '#f8fbf9';
            ?>
            <tr style="background:<?= $rowBg ?>">
                <td style="padding:12px 18px;border-bottom:1px solid #edf5ef;color:#5a7a6e;font-weight:600;font-size:12px">
                    <?= Security::e($row['label']) ?>
                </td>
                <?php foreach ($properties as $p):
                    $raw    = $p[$row['key']] ?? null;
                    $isBest = $bestVal !== null && is_numeric($raw) && (float)$raw === (float)$bestVal;

                    switch ($row['fmt']) {
                        case 'currency':
                            $cell = '<strong style="color:' . ($isBest ? '#235347' : '#051F20') . '">' . Str::currency((float)($raw ?? 0)) . '</strong>';
                            break;
                        case 'bool':
                            $cell = $raw
                                ? '<span style="color:#235347;font-weight:700">&#10003; Yes</span>'
                                : '<span style="color:#c4d6cc">No</span>';
                            break;
                        case 'number':
                            $cell = '<strong style="color:#051F20">' . ($raw ?? '—') . '</strong>';
                            break;
                        case 'walk':
                            $cell = $raw !== null
                                ? '<span style="font-weight:700;color:' . ((int)$raw <= 10 ? '#235347' : ((int)$raw <= 20 ? '#92400e' : '#991b1b')) . '">' . (int)$raw . ' min</span>'
                                : '<span style="color:#c4d6cc">—</span>';
                            break;
                        case 'date':
                            $cell = $raw ? '<span style="color:#051F20">' . date('d M Y', strtotime($raw)) . '</span>' : '<span style="color:#235347;font-weight:700">Now</span>';
                            break;
                        default:
                            $cell = $raw
                                ? '<span style="color:#051F20">' . Security::e(ucwords(str_replace('_',' ',(string)$raw))) . '</span>'
                                : '<span style="color:#c4d6cc">—</span>';
                    }
                ?>
                <td style="padding:12px 18px;border-bottom:1px solid #edf5ef;text-align:center;<?= $isBest ? 'background:#f0fdf4;' : '' ?>">
                    <?= $cell ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>

            <!-- Action row -->
            <tr style="background:#f8fbf9">
                <td style="padding:14px 18px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#5a7a6e">Request Viewing</td>
                <?php foreach ($properties as $p): ?>
                <td style="padding:14px 18px;text-align:center">
                    <?php if ($p['status'] === 'verified'): ?>
                    <a href="<?= Url::base('renter/viewings/request/' . $p['id']) ?>"
                       class="btn btn-primary btn-sm">Book Viewing</a>
                    <?php else: ?>
                    <span style="font-size:12px;color:#c4d6cc">Not available</span>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>

            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>
