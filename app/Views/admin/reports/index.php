<?php use App\Helpers\{Url, Security}; ?>

<div class="row g-4">
    <?php
    $reportCards = [
        ['title'=>'Property Report',  'desc'=>'Registrations, status, verification, rent ranges by date.', 'icon'=>'fa-building',     'color'=>'primary', 'url'=>'admin/reports/properties'],
        ['title'=>'Revenue Report',   'desc'=>'Payments by date range, type, and gateway breakdown.',       'icon'=>'fa-coins',        'color'=>'success', 'url'=>'admin/reports/revenue'],
        ['title'=>'Viewings Report',  'desc'=>'Viewing requests, fees collected, completion rates.',        'icon'=>'fa-calendar-check','color'=>'info',   'url'=>'admin/reports/viewings'],
    ];
    foreach ($reportCards as $r): ?>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center p-5">
                <div class="bg-<?= $r['color'] ?> bg-opacity-10 text-<?= $r['color'] ?> rounded-3 d-inline-flex align-items-center justify-content-center mb-4" style="width:64px;height:64px;font-size:1.75rem">
                    <i class="fa-solid <?= $r['icon'] ?>"></i>
                </div>
                <h5 class="fw-bold"><?= $r['title'] ?></h5>
                <p class="text-muted small mb-4"><?= $r['desc'] ?></p>
                <a href="<?= Url::base($r['url']) ?>" class="btn btn-<?= $r['color'] ?> px-4">View Report</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white fw-bold border-0 py-3">Quick Export</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><a href="<?= Url::base('admin/reports/export/properties?date_from=' . date('Y-m-01') . '&date_to=' . date('Y-m-d')) ?>" class="btn btn-outline-primary w-100"><i class="fa-solid fa-file-csv me-2"></i>Properties CSV</a></div>
            <div class="col-md-4"><a href="<?= Url::base('admin/reports/export/revenue?date_from=' . date('Y-m-01') . '&date_to=' . date('Y-m-d')) ?>" class="btn btn-outline-success w-100"><i class="fa-solid fa-file-csv me-2"></i>Revenue CSV</a></div>
            <div class="col-md-4"><a href="<?= Url::base('admin/reports/export/viewings?date_from=' . date('Y-m-01') . '&date_to=' . date('Y-m-d')) ?>" class="btn btn-outline-info w-100"><i class="fa-solid fa-file-csv me-2"></i>Viewings CSV</a></div>
        </div>
    </div>
</div>
