<?php
/**
 * Property Condition module — admin partial.
 * Expects: $property (array), $conditions (array of reports).
 */
use App\Helpers\{Url, Security, Upload};
use App\Models\PropertyConditionModel;

$levels = PropertyConditionModel::LEVELS;
$levelMeta = PropertyConditionModel::LEVEL_META;
$aspects = PropertyConditionModel::ASPECTS;
?>
<div class="card border-0 shadow-sm mb-4" id="condition">
    <div class="card-header bg-white fw-bold border-0 d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-clipboard-check text-primary me-2"></i>Property Condition</span>
        <span class="badge bg-light text-dark border"><?= count($conditions) ?> report<?= count($conditions) === 1 ? '' : 's' ?></span>
    </div>
    <div class="card-body">

        <?php if (!$conditions): ?>
        <p class="text-muted small mb-3">No condition report yet. Add the first assessment below — the newest active report is shown on the public property page.</p>
        <?php endif; ?>

        <?php foreach ($conditions as $c): ?>
        <?php $photos = PropertyConditionModel::photos($c['photos'] ?? null); ?>
        <div class="border rounded-3 p-3 mb-3" style="border-color:#d6ede0 !important">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                <div>
                    <?php $meta = $levelMeta[$c['overall_condition']] ?? $levelMeta['good']; ?>
                    <span class="status-pill" style="background:<?= $meta['bg'] ?>;color:<?= $meta['color'] ?>;border-color:<?= $meta['color'] ?>33">
                        <?= Security::e($meta['label']) ?>
                    </span>
                    <?php if ($c['active']): ?>
                    <span class="status-pill status-verified ms-1">Shown publicly</span>
                    <?php endif; ?>
                    <span class="text-muted small ms-2"><?= date('d M Y', strtotime($c['assessment_date'] ?: $c['created_at'])) ?>
                        &bull; by <?= Security::e(trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''))) ?></span>
                </div>
                <div class="text-nowrap">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCond<?= $c['id'] ?>">Edit</button>
                    <form method="POST" action="<?= Url::base('admin/properties/' . $property['id'] . '/conditions/' . $c['id'] . '/delete') ?>" class="d-inline">
                        <?= Security::csrfField() ?>
                        <button class="btn btn-sm btn-outline-danger" data-confirm="Delete this condition report and its photos?">&times;</button>
                    </form>
                </div>
            </div>

            <?php
            $rated = array_filter($aspects, fn($k) => !empty($c[$k]), ARRAY_FILTER_USE_KEY);
            if ($rated):
            ?>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <?php foreach ($aspects as $key => $label): if (empty($c[$key])) continue; $m = $levelMeta[$c[$key]]; ?>
                <span class="badge" style="background:<?= $m['bg'] ?>;color:<?= $m['color'] ?>;border:1px solid <?= $m['color'] ?>33;font-weight:600"><?= Security::e($label) ?>: <?= Security::e($m['label']) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($c['summary'])): ?><p class="small mb-1"><?= nl2br(Security::e($c['summary'])) ?></p><?php endif; ?>
            <?php if (!empty($c['issues'])): ?><p class="small mb-1" style="color:#991b1b"><strong>Known issues:</strong> <?= nl2br(Security::e($c['issues'])) ?></p><?php endif; ?>
            <?php if (!empty($c['internal_notes'])): ?><p class="small mb-1 text-muted"><strong>Internal:</strong> <?= nl2br(Security::e($c['internal_notes'])) ?></p><?php endif; ?>

            <?php if ($photos): ?>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <?php foreach ($photos as $i => $p): ?>
                <div style="position:relative">
                    <img src="<?= Url::image($p['path']) ?>" alt="" style="width:76px;height:56px;object-fit:cover;border-radius:6px;border:1px solid #d6ede0">
                    <form method="POST" action="<?= Url::base('admin/properties/' . $property['id'] . '/conditions/' . $c['id'] . '/photos/' . $i . '/delete') ?>"
                          onsubmit="return confirm('Remove this photo?')" style="position:absolute;top:-6px;right:-6px">
                        <?= Security::csrfField() ?>
                        <button class="btn btn-sm btn-danger rounded-circle" style="width:20px;height:20px;line-height:1;font-size:11px;padding:0">&times;</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Edit modal -->
        <div class="modal fade" id="editCond<?= $c['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit condition report</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST" action="<?= Url::base('admin/properties/' . $property['id'] . '/conditions/' . $c['id'] . '/update') ?>" enctype="multipart/form-data">
                    <?= Security::csrfField() ?>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Overall condition</label>
                                <select name="overall_condition" class="form-select">
                                    <?php foreach ($levels as $lv): ?>
                                    <option value="<?= $lv ?>" <?= $c['overall_condition'] === $lv ? 'selected' : '' ?>><?= Security::e($levelMeta[$lv]['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php foreach ($aspects as $key => $label): ?>
                            <div class="col-md-4">
                                <label class="form-label fw-medium"><?= Security::e($label) ?></label>
                                <select name="<?= $key ?>" class="form-select">
                                    <option value="">— not assessed —</option>
                                    <?php foreach ($levels as $lv): ?>
                                    <option value="<?= $lv ?>" <?= ($c[$key] ?? '') === $lv ? 'selected' : '' ?>><?= Security::e($levelMeta[$lv]['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endforeach; ?>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Assessment date</label>
                                <input type="date" name="assessment_date" class="form-control" value="<?= Security::e($c['assessment_date'] ?: date('Y-m-d')) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Summary (shown to renters)</label>
                                <textarea name="summary" class="form-control" rows="2"><?= Security::e($c['summary']) ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Known issues (shown to renters)</label>
                                <textarea name="issues" class="form-control" rows="2"><?= Security::e($c['issues']) ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Internal notes (admin only)</label>
                                <textarea name="internal_notes" class="form-control" rows="2"><?= Security::e($c['internal_notes']) ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Add photos</label>
                                <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
                                <div class="form-text">JPEG/PNG/WebP/GIF, up to 5 MB each. Existing photos are kept.</div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="active" class="form-check-input" value="1" id="cActive<?= $c['id'] ?>" <?= $c['active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="cActive<?= $c['id'] ?>">Show on public property page (deactivates other reports)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save changes</button></div>
                </form>
            </div></div>
        </div>
        <?php endforeach; ?>

        <!-- Add new report -->
        <details <?= $conditions ? '' : 'open' ?>>
            <summary class="fw-semibold small text-primary" style="cursor:pointer">+ Add new condition report</summary>
            <form method="POST" action="<?= Url::base('admin/properties/' . $property['id'] . '/conditions') ?>" enctype="multipart/form-data" class="mt-3">
                <?= Security::csrfField() ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Overall condition</label>
                        <select name="overall_condition" class="form-select" required>
                            <?php foreach ($levels as $lv): ?>
                            <option value="<?= $lv ?>" <?= $lv === 'good' ? 'selected' : '' ?>><?= Security::e($levelMeta[$lv]['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php foreach ($aspects as $key => $label): ?>
                    <div class="col-md-4">
                        <label class="form-label fw-medium"><?= Security::e($label) ?></label>
                        <select name="<?= $key ?>" class="form-select">
                            <option value="">— not assessed —</option>
                            <?php foreach ($levels as $lv): ?>
                            <option value="<?= $lv ?>"><?= Security::e($levelMeta[$lv]['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endforeach; ?>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Assessment date</label>
                        <input type="date" name="assessment_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Summary (shown to renters)</label>
                        <textarea name="summary" class="form-control" rows="2" placeholder="e.g. Recently repainted; plumbing and electrical in good working order."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Known issues (shown to renters)</label>
                        <textarea name="issues" class="form-control" rows="2" placeholder="e.g. Small crack on kitchen tile; wardrobe handle loose."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Internal notes (admin only)</label>
                        <textarea name="internal_notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Photos</label>
                        <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="active" class="form-check-input" value="1" id="newActive" checked>
                            <label class="form-check-label" for="newActive">Show on public property page (deactivates other reports)</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary">Save condition report</button>
                    </div>
                </div>
            </form>
        </details>
    </div>
</div>
