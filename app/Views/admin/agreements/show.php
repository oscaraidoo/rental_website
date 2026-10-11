<?php use App\Helpers\{Url, Security}; ?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

    <div class="table-card">
        <div class="table-card-head">
            <span class="table-card-title"><?= Security::e($agreement['agreement_ref']) ?></span>
        </div>
        <div style="padding:24px">
            <div class="info-row">
                <span class="info-label">Landlord</span>
                <span class="info-value"><?= Security::e($agreement['landlord_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value"><?= Security::e($agreement['email']) ?></span>
            </div>
            <?php if ($agreement['property_title']): ?>
            <div class="info-row">
                <span class="info-label">Property</span>
                <span class="info-value"><?= Security::e($agreement['property_title']) ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label">Type</span>
                <span class="info-value">
                    <?= ucwords(str_replace('_',' ',$agreement['agreement_type'])) ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Version</span>
                <span class="info-value"><?= Security::e($agreement['version']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="status-pill
                    <?= $agreement['status']==='signed' ? 'status-verified'
                      : ($agreement['status']==='sent'  ? 'status-pending' : 'status-draft') ?>">
                    <?= ucfirst($agreement['status']) ?>
                </span>
            </div>
            <?php if ($agreement['commission_rate']): ?>
            <div class="info-row">
                <span class="info-label">Commission Rate</span>
                <span class="info-value"><?= Security::e($agreement['commission_rate']) ?>%</span>
            </div>
            <?php endif; ?>
            <?php if ($agreement['signed_at']): ?>
            <div class="info-row">
                <span class="info-label">Signed At</span>
                <span class="info-value">
                    <?= date('d M Y H:i', strtotime($agreement['signed_at'])) ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-head">
            <span class="table-card-title">Signatures (<?= count($signatures) ?>)</span>
        </div>
        <?php if (empty($signatures)): ?>
        <div style="padding:32px 20px;text-align:center;font-size:13px;color:var(--muted)">
            Not yet signed
        </div>
        <?php else: ?>
        <?php foreach ($signatures as $sig): ?>
        <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px">
                <div style="font-size:13px;font-weight:600">
                    <?= Security::e($sig['signer_name']) ?>
                </div>
                <span class="status-pill
                    <?= $sig['signature_type']==='accept' ? 'status-verified' : 'status-rejected' ?>"
                      style="font-size:10px;white-space:nowrap">
                    <?= ucfirst($sig['signature_type']) ?>
                </span>
            </div>
            <div style="font-size:11px;color:var(--muted);margin-top:4px">
                <?= date('d M Y H:i', strtotime($sig['signed_at'])) ?>
                &bull; IP: <span style="font-family:var(--mono)"><?= Security::e($sig['ip_address']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        <div style="padding:16px 20px;border-top:1px solid var(--border)">
            <a href="<?= Url::base('admin/agreements') ?>"
               class="btn btn-secondary" style="width:100%;text-align:center;display:block">
                Back to Agreements
            </a>
        </div>
    </div>

</div>
