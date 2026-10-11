<?php
/**
 * Notification bell + AJAX dropdown.
 * Expects: $unread (int), $notifRoute (string), $recentNotifs (array), $markAllUrl (string|null)
 */
use App\Helpers\{Url, Security};

// ── Helper functions defined FIRST (before any HTML) ─────────
if (!function_exists('_notif_meta_bell')) {
    function _notif_meta_bell(string $type): array {
        $map = [
            'property_approved'  => ['icon'=>'check',    'color'=>'#16a34a'],
            'property_verified'  => ['icon'=>'shield',   'color'=>'#16a34a'],
            'property_rejected'  => ['icon'=>'x',        'color'=>'#dc2626'],
            'property_suspended' => ['icon'=>'pause',    'color'=>'#d97706'],
            'property_featured'  => ['icon'=>'star',     'color'=>'#d97706'],
            'viewing_requested'  => ['icon'=>'calendar', 'color'=>'#2563eb'],
            'viewing_confirmed'  => ['icon'=>'cal-ok',   'color'=>'#16a34a'],
            'viewing_cancelled'  => ['icon'=>'cal-x',    'color'=>'#dc2626'],
            'payment_successful' => ['icon'=>'receipt',  'color'=>'#16a34a'],
            'payment_failed'     => ['icon'=>'alert',    'color'=>'#dc2626'],
            'new_message'        => ['icon'=>'mail',     'color'=>'#2563eb'],
        ];
        return $map[$type] ?? ['icon'=>'bell','color'=>'#64748b'];
    }
}

if (!function_exists('_notif_icon_bell')) {
    function _notif_icon_bell(string $k): string {
        $p = [
            'bell'     => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
            'check'    => '<polyline points="20 6 9 17 4 12"/>',
            'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
            'x'        => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
            'pause'    => '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>',
            'star'     => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
            'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
            'cal-ok'   => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11 18 15 14"/>',
            'cal-x'    => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="10" y1="15" x2="14" y2="19"/><line x1="14" y1="15" x2="10" y2="19"/>',
            'receipt'  => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1z"/>',
            'alert'    => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
            'mail'     => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        ];
        $d = $p[$k] ?? $p['bell'];
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $d . '</svg>';
    }
}

if (!function_exists('_notif_time_bell')) {
    function _notif_time_bell(string $dt): string {
        $d = time() - strtotime($dt);
        if ($d < 60)     return 'just now';
        if ($d < 3600)   return floor($d/60).'m ago';
        if ($d < 86400)  return floor($d/3600).'h ago';
        if ($d < 604800) return floor($d/86400).'d ago';
        return date('d M Y', strtotime($dt));
    }
}

$_csrf = Security::csrfToken();
$_base = rtrim(\App\Helpers\Url::base(), '/');
?>
<div class="notif-bell-wrap" id="notifBellWrap">

    <!-- Bell button -->
    <button class="nav-notification-btn" id="notifBellBtn"
            aria-label="Notifications" aria-expanded="false" aria-controls="notifDropdown"
            type="button">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        <span class="notif-badge" id="notifBadge"
              style="<?= $unread > 0 ? '' : 'display:none' ?>">
            <?= $unread > 9 ? '9+' : $unread ?>
        </span>
    </button>

    <!-- Dropdown panel -->
    <div class="notif-dropdown" id="notifDropdown" role="dialog" aria-label="Notifications">

        <!-- Header -->
        <div class="notif-dropdown-head">
            <span class="notif-dropdown-title">Notifications</span>
            <button class="notif-mark-all-btn" id="notifMarkAll"
                    data-url="<?= htmlspecialchars($_base . '/api/notifications/read-all') ?>"
                    data-csrf="<?= htmlspecialchars($_csrf) ?>"
                    type="button">
                Mark all read
            </button>
        </div>

        <!-- List -->
        <div class="notif-dropdown-list" id="notifList">
            <?php if (empty($recentNotifs)): ?>
            <div class="notif-empty" id="notifEmpty">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.5" style="opacity:.3;margin-bottom:8px">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <p>You're all caught up</p>
            </div>
            <?php else: ?>
            <?php foreach ($recentNotifs as $n):
                $meta = _notif_meta_bell($n['type'] ?? '');
            ?>
            <div class="notif-row <?= empty($n['is_read']) ? 'notif-row--unread' : '' ?>"
                 data-id="<?= (int)$n['id'] ?>"
                 style="--nc:<?= $meta['color'] ?>">

                <div class="notif-row-icon"><?= _notif_icon_bell($meta['icon']) ?></div>

                <div class="notif-row-body">
                    <div class="notif-row-title"><?= Security::e($n['title']) ?></div>
                    <div class="notif-row-text"><?= Security::e(\App\Helpers\Str::truncate($n['body'], 80)) ?></div>
                    <div class="notif-row-time"><?= _notif_time_bell($n['created_at']) ?></div>
                </div>

                <div class="notif-row-btns">
                    <?php if (empty($n['is_read'])): ?>
                    <button class="notif-row-read" title="Mark as read"
                            data-id="<?= (int)$n['id'] ?>"
                            data-url="<?= htmlspecialchars($_base . '/api/notifications/' . $n['id'] . '/read') ?>"
                            data-csrf="<?= htmlspecialchars($_csrf) ?>"
                            type="button" aria-label="Mark as read">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                    <?php endif; ?>
                    <button class="notif-row-del" title="Delete"
                            data-id="<?= (int)$n['id'] ?>"
                            data-url="<?= htmlspecialchars($_base . '/api/notifications/' . $n['id'] . '/delete') ?>"
                            data-csrf="<?= htmlspecialchars($_csrf) ?>"
                            type="button" aria-label="Delete notification">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <?php if (empty($n['is_read'])): ?>
                <span class="notif-row-dot"></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <a href="<?= Url::base($notifRoute) ?>" class="notif-dropdown-footer">
            View all notifications
        </a>
    </div>
</div>
