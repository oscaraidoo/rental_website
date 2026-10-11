<?php use App\Helpers\{Url, Security, Str}; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <p style="font-size:13px;color:#5a7a6e;margin:0"><?= $pager->info() ?></p>
    <a href="<?= Url::base('properties') ?>" class="btn btn-primary btn-sm">Find more properties</a>
</div>

<?php if (empty($saved)): ?>
<div class="empty-state">
    <div class="empty-state-title">No saved properties</div>
    <div class="empty-state-sub">Browse properties and save the ones you like to compare them later.</div>
    <a href="<?= Url::base('properties') ?>" class="btn btn-primary btn-sm">Browse properties</a>
</div>
<?php else: ?>

<div class="pcard-grid">
    <?php foreach ($saved as $p):
        /* Enrich $p so property_card.php can render it — saved view has slightly different keys */
        $p['primary_thumb']       = $p['primary_thumb'] ?? null;
        $p['primary_image']       = $p['primary_thumb'] ?? null;
        $p['walking_time_minutes']= null; // not returned by getSavedForUser
        $p['vehicle_accessible']  = null;
        $p['is_saved']            = true;
    ?>

    <!-- Wrapper adds the remove button on top of the standard card -->
    <div style="position:relative">
        <?php include ROOT_PATH . '/app/Views/partials/property_card.php'; ?>

        <!-- Remove from saved -->
        <form method="POST"
              action="<?= Url::base('renter/saved/' . $p['id'] . '/remove') ?>"
              style="position:absolute;top:10px;right:46px;z-index:10">
            <?= Security::csrfField() ?>
            <button style="width:32px;height:32px;border-radius:50%;
                           background:rgba(255,255,255,.92);border:1.5px solid #d6ede0;
                           font-size:15px;cursor:pointer;display:flex;align-items:center;
                           justify-content:center;color:#5a7a6e;
                           backdrop-filter:blur(4px);transition:.15s"
                    data-confirm="Remove from saved?"
                    title="Remove from saved">
                &times;
            </button>
        </form>
    </div>

    <?php endforeach; ?>
</div>

<?php if ($pager->lastPage > 1): ?>
<div style="margin-top:32px"><?= $pager->render() ?></div>
<?php endif; ?>
<?php endif; ?>
