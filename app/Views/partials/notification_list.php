<?php
/**
 * Reusable notification list.
 * Expects: $items (array of notification rows), $dropdown (bool, compact mode).
 * Icons/colours come from a local map of known notification types.
 */
use App\Helpers\{Url, Security};

if (!function_exists('_notif_meta')) {
    /** Icon name + colour + fallback label per known notification type */
    function _notif_meta(string $type): array
    {
        $map = [
            'property_approved'   => ['icon' => 'check',      'color' => '#16a34a'],
            'property_verified'   => ['icon' => 'shield',     'color' => '#16a34a'],
            'property_rejected'   => ['icon' => 'x',          'color' => '#dc2626'],
            'property_suspended'  => ['icon' => 'pause',      'color' => '#d97706'],
            'property_featured'   => ['icon' => 'star',       'color' => '#d97706'],
            'property_rented'     => ['icon' => 'home',       'color' => '#235347'],
            'property_submitted'  => ['icon' => 'home-plus',  'color' => '#64748b'],
            'viewing_requested'   => ['icon' => 'calendar',   'color' => '#2563eb'],
            'viewing_confirmed'   => ['icon' => 'calendar-check', 'color' => '#16a34a'],
            'viewing_cancelled'   => ['icon' => 'calendar-x', 'color' => '#dc2626'],
            'viewing_completed'   => ['icon' => 'check',      'color' => '#235347'],
            'payment_successful'  => ['icon' => 'receipt',    'color' => '#16a34a'],
            'payment_failed'      => ['icon' => 'alert',      'color' => '#dc2626'],
            'agreement_signed'    => ['icon' => 'file',       'color' => '#235347'],
            'account_verified'    => ['icon' => 'user-check', 'color' => '#16a34a'],
            'new_message'         => ['icon' => 'mail',       'color' => '#2563eb'],
            'review_posted'       => ['icon' => 'star',       'color' => '#d97706'],
            'report_received'     => ['icon' => 'flag',       'color' => '#dc2626'],
        ];
        return $map[$type] ?? ['icon' => 'bell', 'color' => '#64748b'];
    }
}

if (!function_exists('_notif_icon')) {
    /** Inline SVG icons per registry key (stroke style, inherits currentColor) */
    function _notif_icon(string $icon): string
    {
        $paths = [
            'bell'          => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
            'home'          => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'home-plus'     => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><line x1="12" y1="13" x2="12" y2="18"/><line x1="9.5" y1="15.5" x2="14.5" y2="15.5"/>',
            'check'         => '<polyline points="20 6 9 17 4 12"/>',
            'shield'        => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
            'x'             => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
            'pause'         => '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>',
            'star'          => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
            'calendar'      => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
            'calendar-check'=> '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11 18 15 14"/>',
            'calendar-x'    => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="10" y1="15" x2="14" y2="19"/><line x1="14" y1="15" x2="10" y2="19"/>',
            'receipt'       => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1z"/><polyline points="8 8 16 8"/><polyline points="8 12 16 12"/>',
            'alert'         => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
            'file'          => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
            'user-check'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/>',
            'mail'          => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
            'flag'          => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>',
        ];
        $d = $paths[$icon] ?? $paths['bell'];
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $d . '</svg>';
    }
}

if (!function_exists('_notif_time')) {
    /** Human "time ago" */
    function _notif_time(string $dt): string
    {
        $diff = time() - strtotime($dt);
        if ($diff < 60)     return 'just now';
        if ($diff < 3600)   return floor($diff / 60) . 'm ago';
        if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('d M Y', strtotime($dt));
    }
}
?>
<div class="notif-list">
<?php foreach ($items as $n):
    $meta = _notif_meta($n['type'] ?? '');
    $url  = !empty($n['action_url']) ? Url::base($n['action_url']) : null;
?>
    <div class="notif-item <?= empty($n['is_read']) ? 'notif-item--unread' : '' ?>" style="--notif-color:<?= $meta['color'] ?>">

        <div class="notif-item-icon" aria-hidden="true"><?= _notif_icon($meta['icon']) ?></div>

        <div class="notif-item-body">
            <div class="notif-item-head">
                <span class="notif-item-title"><?= Security::e($n['title']) ?></span>
                <span class="notif-item-time" title="<?= Security::e(date('d M Y H:i', strtotime($n['created_at']))) ?>">
                    <?= _notif_time($n['created_at']) ?>
                </span>
            </div>
            <p class="notif-item-text"><?= Security::e($n['body']) ?></p>

            <div class="notif-item-actions">
                <?php if ($url): ?>
                <a href="<?= $url ?>" class="notif-item-link"
                   <?= empty($n['is_read']) ? ' data-mark-read="' . (int)$n['id'] . '"' : '' ?>>
                    View &rarr;
                </a>
                <?php endif; ?>
                <?php if (empty($n['is_read']) && empty($dropdown)): ?>
                <form method="POST" action="<?= Url::base('notifications/' . $n['id'] . '/read') ?>" class="notif-read-form">
                    <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
                    <button type="submit" class="notif-item-readbtn">Mark as read</button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($n['is_read'])): ?><span class="notif-item-dot" title="Unread"></span><?php endif; ?>
    </div>
<?php endforeach; ?>
</div>

<style>
.notif-list{display:flex;flex-direction:column}
.notif-item{display:flex;gap:12px;align-items:flex-start;padding:13px 16px;position:relative;
            border-bottom:1px solid var(--border,#ece7df);background:#fff;transition:background .15s}
.notif-item:last-child{border-bottom:none}
.notif-item:hover{background:#faf9f6}
.notif-item--unread{background:#f6fbf8}
.notif-item--unread:hover{background:#eef7f2}
.notif-item-icon{width:36px;height:36px;border-radius:10px;flex-shrink:0;display:flex;
                 align-items:center;justify-content:center;color:var(--notif-color,#64748b);
                 background:color-mix(in srgb, var(--notif-color,#64748b) 10%, #fff)}
.notif-item-body{flex:1;min-width:0}
.notif-item-head{display:flex;justify-content:space-between;align-items:baseline;gap:8px}
.notif-item-title{font-size:13px;font-weight:700;color:var(--black,#051F20);letter-spacing:-.01em}
.notif-item-time{font-size:11px;color:#8ba092;white-space:nowrap}
.notif-item-text{font-size:12.5px;line-height:1.55;color:#4a6457;margin:3px 0 0;overflow-wrap:break-word}
.notif-item-actions{display:flex;gap:14px;align-items:center;margin-top:7px}
.notif-item-link{font-size:12px;font-weight:700;color:var(--notif-color,#235347);text-decoration:none}
.notif-item-link:hover{text-decoration:underline}
.notif-item-readbtn{background:none;border:none;padding:0;font-size:12px;font-weight:600;
                    color:#8ba092;cursor:pointer;font-family:inherit}
.notif-item-readbtn:hover{color:#4a6457}
.notif-item-dot{width:8px;height:8px;border-radius:50%;background:var(--notif-color,#2563eb);
                flex-shrink:0;margin-top:6px}
.notif-read-form{display:inline}
</style>
