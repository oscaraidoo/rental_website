<?php use App\Helpers\{Url, Security}; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start">

    <!-- Detail -->
    <div class="table-card">
        <div class="table-card-head">
            <span class="table-card-title">Report Details</span>
        </div>
        <div style="padding:24px">
            <div class="info-row">
                <span class="info-label">Property</span>
                <a href="<?= Url::base('admin/properties/' . $report['property_id']) ?>"
                   style="font-size:13px;font-weight:600;color:var(--black)">
                    <?= Security::e($report['property_title']) ?>
                </a>
            </div>
            <div class="info-row">
                <span class="info-label">Reference</span>
                <span class="code"><?= Security::e($report['reference']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Reporter</span>
                <span class="info-value">
                    <?= Security::e($report['reporter_name'] ?? 'Anonymous') ?>
                    <?php if ($report['reporter_email']): ?>
                    <span style="color:var(--muted);font-size:12px;display:block">
                        <?= Security::e($report['reporter_email']) ?>
                    </span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Report Type</span>
                <span class="info-value"><?= ucwords(str_replace('_',' ',$report['report_type'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="status-pill
                    <?= $report['status']==='resolved' ? 'status-verified'
                      : ($report['status']==='open'     ? 'status-pending' : 'status-draft') ?>">
                    <?= ucfirst($report['status']) ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Submitted</span>
                <span class="info-value">
                    <?= date('d M Y H:i', strtotime($report['created_at'])) ?>
                </span>
            </div>

            <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                             letter-spacing:.08em;color:var(--muted);margin-bottom:10px">
                    Description
                </div>
                <p style="font-size:14px;line-height:1.7;color:var(--dark);margin:0">
                    <?= Security::e($report['description']) ?>
                </p>
            </div>

            <?php if ($report['resolution']): ?>
            <div style="margin-top:20px;padding:16px;background:var(--lighter);
                        border-radius:var(--radius-sm);border:1.5px solid var(--border)">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                             letter-spacing:.08em;color:var(--muted);margin-bottom:8px">
                    Resolution
                </div>
                <p style="font-size:13px;line-height:1.65;color:var(--dark);margin:0">
                    <?= Security::e($report['resolution']) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Action -->
    <div class="table-card">
        <div class="table-card-head">
            <span class="table-card-title">Update Status</span>
        </div>
        <div style="padding:24px">
            <form method="POST"
                  action="<?= Url::base('admin/complaints/' . $report['id'] . '/resolve') ?>">
                <?= \App\Helpers\Security::csrfField() ?>
                <div style="margin-bottom:16px">
                    <label class="form-label">New Status</label>
                    <select name="status" class="form-select">
                        <option value="investigating">Investigating</option>
                        <option value="resolved">Resolved</option>
                        <option value="dismissed">Dismissed</option>
                    </select>
                </div>
                <div style="margin-bottom:20px">
                    <label class="form-label">Resolution Notes</label>
                    <textarea name="resolution" class="form-control" rows="4"
                              placeholder="Describe what was found and what action was taken..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">
                    Update Report
                </button>
            </form>
            <a href="<?= Url::base('admin/complaints') ?>"
               class="btn btn-secondary" style="width:100%;margin-top:10px;text-align:center;display:block">
                Back to Complaints
            </a>
        </div>
    </div>
</div>
