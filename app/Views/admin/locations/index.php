<?php use App\Helpers\{Url, Security}; ?>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;align-items:start">

    <!-- Regions -->
    <div class="table-card">
        <div class="table-card-head">
            <span class="table-card-title">Regions (<?= count($regions) ?>)</span>
        </div>
        <div style="max-height:320px;overflow-y:auto">
            <?php foreach ($regions as $r): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:11px 20px;border-bottom:1px solid var(--border)">
                <div>
                    <div style="font-size:13px;font-weight:600"><?= Security::e($r['name']) ?></div>
                    <div style="font-size:11px;color:var(--muted)"><?= $r['city_count'] ?> cities</div>
                </div>
                <form method="POST"
                      action="<?= Url::base('admin/locations/region/' . $r['id'] . '/delete') ?>">
                    <?= Security::csrfField() ?>
                    <button class="btn btn-sm"
                            style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b;padding:3px 10px"
                            data-confirm="Delete region &quot;<?= Security::e($r['name']) ?>&quot;? Only if no properties are linked.">
                        Delete
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="padding:16px 20px;border-top:1px solid var(--border)">
            <form method="POST" action="<?= Url::base('admin/locations/region') ?>"
                  style="display:flex;gap:8px">
                <?= Security::csrfField() ?>
                <input type="text" name="name" class="form-control"
                       placeholder="New region name" required style="flex:1">
                <button class="btn btn-primary btn-sm">Add</button>
            </form>
        </div>
    </div>

    <!-- Cities -->
    <div class="table-card">
        <div class="table-card-head">
            <span class="table-card-title">Cities</span>
        </div>
        <div style="max-height:320px;overflow-y:auto">
            <?php foreach ($cities as $c): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:11px 20px;border-bottom:1px solid var(--border)">
                <div>
                    <div style="font-size:13px;font-weight:600"><?= Security::e($c['name']) ?></div>
                    <div style="font-size:11px;color:var(--muted)"><?= Security::e($c['region_name']) ?></div>
                </div>
                <form method="POST"
                      action="<?= Url::base('admin/locations/city/' . $c['id'] . '/delete') ?>">
                    <?= Security::csrfField() ?>
                    <button class="btn btn-sm"
                            style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b;padding:3px 10px"
                            data-confirm="Delete city &quot;<?= Security::e($c['name']) ?>&quot; and its areas?">
                        Delete
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="padding:16px 20px;border-top:1px solid var(--border)">
            <form method="POST" action="<?= Url::base('admin/locations/city') ?>">
                <?= Security::csrfField() ?>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <select name="region_id" class="form-select" required>
                        <option value="">Select region</option>
                        <?php foreach ($regions as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= Security::e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div style="display:flex;gap:8px">
                        <input type="text" name="name" class="form-control"
                               placeholder="City name" required style="flex:1">
                        <button class="btn btn-primary btn-sm">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Areas -->
    <div class="table-card">
        <div class="table-card-head">
            <span class="table-card-title">Areas</span>
        </div>
        <div style="max-height:320px;overflow-y:auto">
            <?php foreach ($areas as $a): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:11px 20px;border-bottom:1px solid var(--border)">
                <div>
                    <div style="font-size:13px;font-weight:600"><?= Security::e($a['name']) ?></div>
                    <div style="font-size:11px;color:var(--muted)"><?= Security::e($a['city_name']) ?></div>
                </div>
                <form method="POST"
                      action="<?= Url::base('admin/locations/area/' . $a['id'] . '/delete') ?>">
                    <?= Security::csrfField() ?>
                    <button class="btn btn-sm"
                            style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b;padding:3px 10px"
                            data-confirm="Delete area &quot;<?= Security::e($a['name']) ?>&quot;?">
                        Delete
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="padding:16px 20px;border-top:1px solid var(--border)">
            <form method="POST" action="<?= Url::base('admin/locations/area') ?>">
                <?= Security::csrfField() ?>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <select name="city_id" class="form-select" required>
                        <option value="">Select city</option>
                        <?php foreach ($cities as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= Security::e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div style="display:flex;gap:8px">
                        <input type="text" name="name" class="form-control"
                               placeholder="Area name" required style="flex:1">
                        <button class="btn btn-primary btn-sm">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
