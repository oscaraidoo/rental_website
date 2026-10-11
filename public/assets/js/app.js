/**
 * RentalWebsite — Main JS
 * No framework. No icon library. Pure DOM.
 */
'use strict';

document.addEventListener('DOMContentLoaded', function () {

    // ── Auto-dismiss success alerts ───────────────────────
    document.querySelectorAll('.alert-success').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity    = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 5000);
    });

    // ── Confirm dialogs ───────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // ── AJAX property save/unsave ─────────────────────────
    document.querySelectorAll('.btn-save-property, .prop-save-btn').forEach(function (btn) {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            e.stopPropagation();

            var propertyId = this.dataset.propertyId;
            if (!propertyId) return;

            var token = document.querySelector('meta[name="csrf-token"]')?.content || '';

            // Build the API URL reliably from the server-injected base URL
            var base = (window.APP_BASE_URL || '').replace(/\/+$/, '');
            var apiUrl = base + '/api/properties/' + propertyId + '/favorite';

            try {
                var res  = await fetch(apiUrl, {
                    method:  'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token':     token,
                        'Content-Type':     'application/json',
                    },
                    body: JSON.stringify({ _csrf_token: token }),
                });
                var data = await res.json();
                if (data.success) {
                    var saved = data.saved;
                    this.classList.toggle('is-saved', saved);
                    this.title = saved ? 'Remove from saved' : 'Save property';
                    // Update SVG fill to reflect saved state without destroying the SVG
                    var svgPath = this.querySelector('path');
                    if (svgPath) {
                        this.querySelector('svg').setAttribute('fill', saved ? '#DAF1DE' : 'none');
                    }
                }
            } catch (err) {
                // Silently fail
            }
        });
    });

    // ── Notification badge polling ─────────────────────────
    function refreshNotifBadge() {
        var base = (window.APP_BASE_URL || '').replace(/\/+$/, '');
        fetch(base + '/api/notifications?count_only=1', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (!data) return;
            var dots = document.querySelectorAll('.notif-dot');
            dots.forEach(function (dot) {
                dot.style.display = data.unread > 0 ? 'block' : 'none';
            });
        })
        .catch(function () {});
    }

    // Poll every 60s when inside a dashboard layout
    if (document.querySelector('.app-sidebar')) {
        refreshNotifBadge();
        setInterval(refreshNotifBadge, 60000);
    }

    // ── Notification bell dropdown ─────────────────────────
    initNotifBell();

    // ── Transparent navbar on hero2 pages ─────────────────
    (function () {
        var nav = document.querySelector('.hero2-page .site-nav');
        if (!nav) return;
        function onScroll() {
            if (window.scrollY > 40) nav.classList.add('scrolled');
            else                     nav.classList.remove('scrolled');
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    })();

    var selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(function (cb) {
                cb.checked = selectAll.checked;
            });
        });
    }

    // ── Character counter for textareas with maxlength ────
    document.querySelectorAll('textarea[maxlength]').forEach(function (ta) {
        var max     = parseInt(ta.maxLength, 10);
        var counter = document.createElement('div');
        counter.style.cssText = 'font-size:11px;color:var(--muted);text-align:right;margin-top:4px;font-weight:500';
        counter.textContent   = '0 / ' + max;
        ta.parentNode.insertBefore(counter, ta.nextSibling);
        ta.addEventListener('input', function () {
            counter.textContent = ta.value.length + ' / ' + max;
            counter.style.color = ta.value.length > max * .9 ? '#dc2626' : 'var(--muted)';
        });
    });

    // ── Password show / hide ──────────────────────────────
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.querySelector(this.dataset.target);
            if (!target) target = this.closest('.input-group')?.querySelector('input');
            if (!target) return;
            var isHidden = target.type === 'password';
            target.type  = isHidden ? 'text' : 'password';
            this.textContent = isHidden ? 'Hide' : 'Show';
        });
    });

    // ── Gallery thumbnail switcher (detail page) ──────────
    document.querySelectorAll('.detail-gallery-thumb').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            var src = this.querySelector('img')?.src;
            if (!src) return;
            // Strip thumbnail prefix
            var fullSrc = src.replace('/thumb_', '/');
            var main    = document.getElementById('galleryMainImg');
            if (main) main.src = fullSrc;

            document.querySelectorAll('.detail-gallery-thumb').forEach(function (t) {
                t.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    // ── Copy to clipboard fallback ────────────────────────
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var text = this.dataset.copy;
            if (!text) return;
            navigator.clipboard.writeText(text).then(function () {
                var orig = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(function () { btn.textContent = orig; }, 2000);
            }).catch(function () {});
        });
    });

    // ── Filter form — tag pill keyboard accessibility ─────
    document.querySelectorAll('.tag input[type="radio"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            this.closest('form')?.submit();
        });
    });

    // ── Admin: highlight active data-table row ────────────
    document.querySelectorAll('.data-table tbody tr').forEach(function (row) {
        row.addEventListener('click', function (e) {
            // Only follow if click wasn't on a button/link/input
            if (['A','BUTTON','INPUT','SELECT','TEXTAREA'].includes(e.target.tagName)) return;
            var link = row.querySelector('a[href]:not([href="#"])');
            if (link) link.click();
        });
        if (document.querySelectorAll('.data-table tbody tr a').length > 0) {
            row.style.cursor = 'pointer';
        }
    });

    // ── Chart.js defaults ─────────────────────────────────
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
        Chart.defaults.font.size   = 12;
        Chart.defaults.color       = '#888888';
        Chart.defaults.plugins.legend.position = 'bottom';
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.pointStyleWidth = 8;
    }

});

// ── Admin charts (called after page-specific data injected) ──
window.initAdminCharts = function (data) {
    if (typeof Chart === 'undefined' || !data) return;

    function makeChart(id, config) {
        var canvas = document.getElementById(id);
        if (!canvas) return;
        // Destroy any existing Chart instance on this canvas before creating a new one
        var existing = Chart.getChart(canvas);
        if (existing) existing.destroy();
        new Chart(canvas, config);
    }

    // Revenue line chart
    makeChart('revenueChart', {
        type: 'line',
        data: {
            labels:   data.revenue?.labels   || [],
            datasets: [{
                label:           'Revenue (GH₵)',
                data:            data.revenue?.values || [],
                borderColor:     '#0a0a0a',
                backgroundColor: 'rgba(10,10,10,.04)',
                tension:         .35,
                fill:            true,
                pointRadius:     4,
                pointBackgroundColor: '#0a0a0a',
                borderWidth:     2,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, border: { color: '#e5e5e5' } },
                y: { grid: { color: '#f5f5f5' }, border: { dash: [4,4], color: 'transparent' } }
            }
        },
    });

    // Properties bar chart
    makeChart('propertiesChart', {
        type: 'bar',
        data: {
            labels:   data.properties?.labels   || [],
            datasets: [{
                label:           'New Properties',
                data:            data.properties?.values || [],
                backgroundColor: '#0a0a0a',
                borderRadius:    3,
                borderSkipped:   false,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, border: { color: '#e5e5e5' } },
                y: { grid: { color: '#f5f5f5' }, border: { dash: [4,4], color: 'transparent' } }
            }
        },
    });

    // Status doughnut
    makeChart('statusChart', {
        type: 'doughnut',
        data: {
            labels:   data.statuses?.labels || [],
            datasets: [{
                data:            data.statuses?.values || [],
                backgroundColor: ['#0a0a0a','#444444','#888888','#bbbbbb','#e5e5e5'],
                borderColor:     '#ffffff',
                borderWidth:     3,
            }],
        },
        options: {
            responsive: true,
            cutout: '68%',
            plugins: { legend: { labels: { color: '#444' } } }
        },
    });
};

/* ════════════════════════════════════════════════════════════
   NOTIFICATION BELL — AJAX interactions
   ════════════════════════════════════════════════════════════ */

function initNotifBell() {
    var btn      = document.getElementById('notifBellBtn');
    var dropdown = document.getElementById('notifDropdown');
    if (!btn || !dropdown) return;

    // ── Toggle open/close ──────────────────────────────────
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var open = dropdown.classList.toggle('open');
        btn.setAttribute('aria-expanded', open);
    });

    // Close when clicking outside
    document.addEventListener('click', function (e) {
        var wrap = document.getElementById('notifBellWrap');
        if (wrap && !wrap.contains(e.target)) {
            dropdown.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        }
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            dropdown.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        }
    });

    // ── Mark single as read ────────────────────────────────
    dropdown.addEventListener('click', function (e) {
        var readBtn = e.target.closest('.notif-row-read');
        if (readBtn) {
            e.stopPropagation();
            var url  = readBtn.dataset.url;
            var csrf = readBtn.dataset.csrf;
            var row  = readBtn.closest('.notif-row');
            notifPost(url, csrf).then(function (data) {
                if (!data.success) return;
                row.classList.remove('notif-row--unread');
                readBtn.remove();
                var dot = row.querySelector('.notif-row-dot');
                if (dot) dot.remove();
                updateNotifBadge(data.unread);
                Toast.success('Marked as read');
            });
        }

        // ── Delete ─────────────────────────────────────────
        var delBtn = e.target.closest('.notif-row-del');
        if (delBtn) {
            e.stopPropagation();
            var url  = delBtn.dataset.url;
            var csrf = delBtn.dataset.csrf;
            var row  = delBtn.closest('.notif-row');
            notifPost(url, csrf).then(function (data) {
                if (!data.success) return;
                row.classList.add('notif-row--removing');
                setTimeout(function () {
                    row.remove();
                    checkNotifEmpty();
                }, 260);
                updateNotifBadge(data.unread);
                Toast.info('Notification removed');
            });
        }
    });

    // ── Mark all as read ───────────────────────────────────
    var markAllBtn = document.getElementById('notifMarkAll');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var url  = markAllBtn.dataset.url;
            var csrf = markAllBtn.dataset.csrf;
            notifPost(url, csrf).then(function (data) {
                if (!data.success) return;
                dropdown.querySelectorAll('.notif-row--unread').forEach(function (r) {
                    r.classList.remove('notif-row--unread');
                    var dot = r.querySelector('.notif-row-dot');
                    if (dot) dot.remove();
                    var rb = r.querySelector('.notif-row-read');
                    if (rb) rb.remove();
                });
                updateNotifBadge(0);
                Toast.success('All notifications marked as read');
            });
        });
    }
}

// ── Helpers ────────────────────────────────────────────────

function notifPost(url, csrf) {
    return fetch(url, {
        method:  'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token':     csrf,
            'Content-Type':     'application/json',
        },
        body: JSON.stringify({ _csrf_token: csrf }),
    })
    .then(function (r) { return r.ok ? r.json() : { success: false }; })
    .catch(function ()  { return { success: false }; });
}

function updateNotifBadge(count) {
    var badge = document.getElementById('notifBadge');
    if (!badge) return;
    if (count <= 0) {
        badge.style.display = 'none';
        badge.textContent = '0';
    } else {
        badge.textContent = count > 9 ? '9+' : count;
        badge.style.display = 'flex';
        badge.classList.add('notif-badge--bump');
        setTimeout(function () { badge.classList.remove('notif-badge--bump'); }, 350);
    }
    // Also update all other notif-dots on page (topbar)
    document.querySelectorAll('.notif-dot').forEach(function (dot) {
        dot.style.display = count > 0 ? 'block' : 'none';
    });
}

function checkNotifEmpty() {
    var list = document.getElementById('notifList');
    if (!list) return;
    var rows = list.querySelectorAll('.notif-row');
    var empty = document.getElementById('notifEmpty');
    if (rows.length === 0) {
        if (!empty) {
            var div = document.createElement('div');
            div.className = 'notif-empty';
            div.id = 'notifEmpty';
            div.innerHTML = '<p>You\'re all caught up &#10003;</p>';
            list.appendChild(div);
        }
    }
}

/* ════════════════════════════════════════════════════════════
   TOAST SYSTEM
   ════════════════════════════════════════════════════════════ */

var Toast = (function () {
    var container;

    function getContainer() {
        if (!container) {
            container = document.getElementById('toastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toastContainer';
                document.body.appendChild(container);
            }
        }
        return container;
    }

    function show(type, title, message, duration) {
        duration = duration || 4000;
        var icons = {
            success: '<polyline points="20 6 9 17 4 12"/>',
            error:   '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
            warning: '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            info:    '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        };

        var toast = document.createElement('div');
        toast.className = 'toast toast--' + type;
        toast.style.position = 'relative';
        toast.innerHTML =
            '<div class="toast-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' + (icons[type] || icons.info) + '</svg></div>' +
            '<div class="toast-body">' +
                '<div class="toast-title">' + escHtml(title) + '</div>' +
                (message ? '<div class="toast-message">' + escHtml(message) + '</div>' : '') +
            '</div>' +
            '<button class="toast-close" aria-label="Close">&times;</button>' +
            '<div class="toast-progress" style="width:100%"></div>';

        var bar = toast.querySelector('.toast-progress');
        var start = null;

        getContainer().appendChild(toast);

        // Animate progress bar
        function step(ts) {
            if (!start) start = ts;
            var pct = Math.max(0, 100 - ((ts - start) / duration) * 100);
            if (bar) bar.style.width = pct + '%';
            if (pct > 0) requestAnimationFrame(step);
            else remove();
        }
        requestAnimationFrame(step);

        function remove() {
            toast.classList.add('toast--removing');
            setTimeout(function () { toast.remove(); }, 220);
        }

        toast.querySelector('.toast-close').addEventListener('click', remove);
    }

    function escHtml(s) {
        return String(s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    return {
        success: function (msg, detail) { show('success', msg, detail || ''); },
        error:   function (msg, detail) { show('error',   msg, detail || ''); },
        warning: function (msg, detail) { show('warning', msg, detail || ''); },
        info:    function (msg, detail) { show('info',    msg, detail || ''); },
    };
})();

// ── Auto-show flash messages as toasts ────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert-success').forEach(function (el) {
        Toast.success(el.textContent.trim());
    });
    document.querySelectorAll('.alert-danger').forEach(function (el) {
        Toast.error(el.textContent.trim());
    });
    document.querySelectorAll('.alert-warning').forEach(function (el) {
        Toast.warning(el.textContent.trim());
    });
});

/* ════════════════════════════════════════════════════════════
   PROFILE MODULE — photo upload + AJAX helpers
   ════════════════════════════════════════════════════════════ */

(function () {
    // ── Avatar photo upload via hidden file input ───────────
    var photoInput  = document.getElementById('profPhotoInput');
    var photoForm   = document.getElementById('profPhotoForm');
    var photoFile   = document.getElementById('profPhotoFormFile');
    var avatarImg   = document.getElementById('profAvatarImg');

    if (photoInput && photoForm && photoFile) {
        photoInput.addEventListener('change', function () {
            if (!this.files || !this.files[0]) return;

            var file = this.files[0];

            // Client-side preview immediately
            var reader = new FileReader();
            reader.onload = function (e) {
                if (!avatarImg) return;
                if (avatarImg.tagName === 'IMG') {
                    avatarImg.src = e.target.result;
                } else {
                    // Replace emoji div with img
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.id  = 'profAvatarImg';
                    img.className = 'prof-avatar-img';
                    img.alt = 'Profile photo';
                    avatarImg.parentNode.replaceChild(img, avatarImg);
                    avatarImg = img;
                }
            };
            reader.readAsDataURL(file);

            // Upload via AJAX
            var fd   = new FormData(photoForm);
            var base = (window.APP_BASE_URL || '').replace(/\/+$/, '');
            var csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

            // Transfer file to hidden form field
            var dt   = new DataTransfer();
            dt.items.add(file);
            photoFile.files = dt.files;
            fd = new FormData(photoForm);
            fd.set('_csrf_token', csrf);

            fetch(base + '/renter/profile/photo', {
                method:  'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrf },
                body:    fd,
            })
            .then(function (r) { return r.ok ? r.json() : { success: false, message: 'Upload failed.' }; })
            .then(function (data) {
                if (data.success) {
                    Toast.success('Profile photo updated');
                    // Update navbar avatar if present
                    document.querySelectorAll('.nav-avatar-img').forEach(function (el) {
                        if (el.tagName === 'IMG') el.src = data.data?.path || el.src;
                    });
                } else {
                    Toast.error(data.message || 'Upload failed. Please try again.');
                }
            })
            .catch(function () { Toast.error('Upload failed. Please check your connection.'); });
        });
    }

    // ── Profile info form — AJAX save (optional, falls back gracefully) ──
    var profInfoForm = document.getElementById('profInfoForm');
    if (profInfoForm) {
        profInfoForm.addEventListener('submit', function (e) {
            // Only intercept if AJAX is desired — let normal submit work as fallback
            // Uncomment the lines below to enable full AJAX submit:
            // e.preventDefault();
            // profAjaxSubmit(this, 'Profile updated successfully.');
        });
    }

    // ── Password strength meter ─────────────────────────────
    var pwInput = document.querySelector('[name="new_password"]');
    if (pwInput && typeof profPwStrength === 'undefined') {
        window.profPwStrength = function (inp) {
            var v = inp.value, score = 0;
            if (v.length >= 8) score++;
            if (/[A-Z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            var bar  = document.getElementById('pwBar');
            var hint = document.getElementById('pwHint');
            var map  = [[0,'transparent',''],[25,'#dc2626','Weak'],[50,'#d97706','Fair'],[75,'#2563eb','Good'],[100,'#16a34a','Strong']];
            var s = map[Math.min(score, 4)];
            if (bar)  { bar.style.width = s[0] + '%'; bar.style.background = s[1]; }
            if (hint) { hint.textContent = s[2]; hint.style.color = s[1]; }
        };
    }

    // ── Profile page specific: show/hide pet field on load ──
    var hasPetsSelect = document.querySelector('[name="has_pets"]');
    if (hasPetsSelect) {
        hasPetsSelect.addEventListener('change', function () {
            var wrap = document.getElementById('petWrap');
            if (wrap) wrap.style.display = this.value === '1' ? '' : 'none';
        });
    }

})();
