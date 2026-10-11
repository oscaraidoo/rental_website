<?php
use App\Helpers\{Url, Security};
$p       = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$active  = fn(string $seg): string => str_contains($p, $seg) ? 'active' : '';

// Pending counts for badges
$pendingProps    = (int)\App\Helpers\Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE status='submitted' AND deleted_at IS NULL") ?: 0;
$pendingComplaints = (int)\App\Helpers\Database::fetchColumn("SELECT COUNT(*) FROM property_reports WHERE status='open'") ?: 0;
?>
<nav class="app-sidebar" id="appSidebar">

    <div class="sidebar-logo">
        <a href="<?= Url::base() ?>" style="color:inherit;text-decoration:none">
            RentalWebsite
        </a>
    </div>

    <div class="sidebar-nav">

        <a href="<?= Url::base('admin/dashboard') ?>" class="sidebar-link <?= $active('admin/dashboard') ?>">
            Dashboard
        </a>

        <div class="sidebar-section">Users</div>
        <a href="<?= Url::base('admin/users') ?>" class="sidebar-link <?= $active('admin/users') ?>">
            All Users
        </a>

        <div class="sidebar-section">Properties</div>
        <a href="<?= Url::base('admin/properties') ?>" class="sidebar-link <?= $active('admin/properties') ?>">
            All Properties
        </a>
        <a href="<?= Url::base('admin/verifications') ?>" class="sidebar-link <?= $active('admin/verifications') ?>">
            Verifications
            <?php if ($pendingProps): ?>
            <span class="sidebar-badge"><?= $pendingProps ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= Url::base('admin/property-types') ?>" class="sidebar-link <?= $active('admin/property-types') ?>">
            Property Types
        </a>
        <a href="<?= Url::base('admin/amenities') ?>" class="sidebar-link <?= $active('admin/amenities') ?>">
            Amenities
        </a>

        <div class="sidebar-section">Viewings</div>
        <a href="<?= Url::base('admin/viewings') ?>" class="sidebar-link <?= $active('admin/viewings') ?>">
            Viewings
        </a>

        <div class="sidebar-section">Finance</div>
        <a href="<?= Url::base('admin/payments') ?>" class="sidebar-link <?= $active('admin/payments') ?>">
            Payments
        </a>
        <a href="<?= Url::base('admin/agreements') ?>" class="sidebar-link <?= $active('admin/agreements') ?>">
            Agreements
        </a>

        <div class="sidebar-section">Content</div>
        <a href="<?= Url::base('admin/cms') ?>" class="sidebar-link <?= $active('admin/cms') ?>">
            CMS Pages
        </a>
        <a href="<?= Url::base('admin/cms/home') ?>" class="sidebar-link <?= $active('admin/cms/home') ?>">
            Home Slides
        </a>
        <a href="<?= Url::base('admin/faqs') ?>" class="sidebar-link <?= $active('admin/faqs') ?>">
            FAQs
        </a>
        <a href="<?= Url::base('admin/blog') ?>" class="sidebar-link <?= $active('admin/blog') ?>">
            Blog
        </a>
        <a href="<?= Url::base('admin/locations') ?>" class="sidebar-link <?= $active('admin/locations') ?>">
            Locations
        </a>

        <div class="sidebar-section">Reports</div>
        <a href="<?= Url::base('admin/reports') ?>" class="sidebar-link <?= $active('admin/reports') ?>">
            Reports
        </a>
        <a href="<?= Url::base('admin/complaints') ?>" class="sidebar-link <?= $active('admin/complaints') ?>">
            Complaints
            <?php if ($pendingComplaints): ?>
            <span class="sidebar-badge"><?= $pendingComplaints ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= Url::base('admin/audit-logs') ?>" class="sidebar-link <?= $active('admin/audit-logs') ?>">
            Audit Logs
        </a>

        <div class="sidebar-section">System</div>
        <a href="<?= Url::base('admin/performance') ?>" class="sidebar-link <?= $active('admin/performance') ?>">
            Performance
        </a>
        <a href="<?= Url::base('admin/settings') ?>" class="sidebar-link <?= $active('admin/settings') ?>">
            Settings
        </a>
        <a href="<?= Url::base('admin/settings/fees') ?>" class="sidebar-link <?= $active('admin/settings/fees') ?>">
            Fees &amp; Commissions
        </a>

        <div style="height:1px;background:var(--border);margin:16px 0"></div>
        <a href="<?= Url::base('auth/logout') ?>" class="sidebar-link"
           style="color:#dc2626">Sign out</a>

    </div>
</nav>
