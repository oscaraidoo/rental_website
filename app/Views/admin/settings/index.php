<?php use App\Helpers\{Url, Security}; ?>

<?php
/** @var array $settings group => rows (from SettingsModel::all()) */
/** @var string $activeGroup currently selected tab */
?>

<div class="row g-4">

    <!-- Side nav — plain links: the tab switch is server-side (?tab=group),
         so it works even if JS or the Bootstrap CDN fails, and each group
         submits its own form. -->
    <div class="col-12 col-lg-3">
        <div class="table-card">
            <div style="padding:8px">
                <?php foreach (array_keys($settings) as $group): ?>
                <a href="<?= Url::base('admin/settings?tab=' . urlencode($group)) ?>"
                   class="sidebar-link <?= $group === $activeGroup ? 'active' : '' ?>"
                   style="margin-bottom:2px">
                    <?= ucwords(str_replace('_', ' ', $group)) ?>
                </a>
                <?php endforeach; ?>
                <div style="height:1px;background:var(--border);margin:8px 0"></div>
                <a href="<?= Url::base('admin/settings/fees') ?>" class="sidebar-link" style="color:#92400e">
                    Fees &amp; Commissions
                </a>
            </div>
        </div>
    </div>

    <!-- Single active panel -->
    <div class="col-12 col-lg-9">
        <?php $rows = $settings[$activeGroup] ?? []; ?>

        <form method="POST" action="<?= Url::base('admin/settings') ?>">
            <?= Security::csrfField() ?>
            <input type="hidden" name="group" value="<?= Security::e($activeGroup) ?>">

            <div class="table-card">
                <div class="table-card-head">
                    <span class="table-card-title">
                        <?= ucwords(str_replace('_', ' ', $activeGroup)) ?> Settings
                    </span>
                </div>

                <div style="padding:24px">
                    <?php if (empty($rows)): ?>
                    <p class="text-muted mb-0">No settings in this group.</p>
                    <?php endif; ?>

                    <?php foreach ($rows as $key => $row): ?>
                    <div style="margin-bottom:20px">
                        <label class="form-label"
                               for="setting_<?= Security::e($key) ?>"
                               style="font-size:13px;font-weight:600;color:var(--dark);margin-bottom:6px;display:block">
                            <?= Security::e($row['label'] ?: ucwords(str_replace('_', ' ', $key))) ?>
                        </label>

                        <?php if ($row['type'] === 'boolean'): ?>
                        <select class="form-select"
                                name="<?= Security::e($key) ?>"
                                id="setting_<?= Security::e($key) ?>"
                                style="max-width:200px">
                            <option value="1" <?= $row['cast_value'] ? 'selected' : '' ?>>Yes</option>
                            <option value="0" <?= !$row['cast_value'] ? 'selected' : '' ?>>No</option>
                        </select>

                        <?php elseif ($row['type'] === 'text'): ?>
                        <textarea class="form-control"
                                  name="<?= Security::e($key) ?>"
                                  id="setting_<?= Security::e($key) ?>"
                                  rows="3"><?= Security::e($row['value'] ?? '') ?></textarea>

                        <?php elseif ($row['type'] === 'integer'): ?>
                        <input type="number" step="1"
                               class="form-control"
                               style="max-width:200px"
                               name="<?= Security::e($key) ?>"
                               id="setting_<?= Security::e($key) ?>"
                               value="<?= Security::e($row['value'] ?? '') ?>">

                        <?php elseif ($row['type'] === 'float'): ?>
                        <input type="number" step="0.01"
                               class="form-control"
                               style="max-width:200px"
                               name="<?= Security::e($key) ?>"
                               id="setting_<?= Security::e($key) ?>"
                               value="<?= Security::e($row['value'] ?? '') ?>">

                        <?php else: ?>
                        <input type="text"
                               class="form-control"
                               name="<?= Security::e($key) ?>"
                               id="setting_<?= Security::e($key) ?>"
                               value="<?= Security::e($row['value'] ?? '') ?>">
                        <?php endif; ?>

                        <?php if (!empty($row['description'])): ?>
                        <div class="form-text"><?= Security::e($row['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="padding:16px 24px;border-top:1px solid var(--border)">
                    <button type="submit" class="btn btn-primary">
                        Save <?= ucwords(str_replace('_', ' ', $activeGroup)) ?> Settings
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
