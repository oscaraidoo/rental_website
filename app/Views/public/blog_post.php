<?php use App\Helpers\{Url, Security, Str}; ?>

<!-- ── Breadcrumb ─────────────────────────────────────────── -->
<div style="background:#fff;border-bottom:1.5px solid var(--border);padding:12px 0">
    <div style="max-width:860px;margin:0 auto;padding:0 24px">
        <nav style="font-size:12px;color:var(--muted);display:flex;align-items:center;gap:6px;flex-wrap:wrap">
            <a href="<?= Url::base() ?>"    style="color:var(--muted);font-weight:500;text-decoration:none">Home</a>
            <span>/</span>
            <a href="<?= Url::base('blog') ?>" style="color:var(--muted);font-weight:500;text-decoration:none">Blog</a>
            <span>/</span>
            <span style="color:var(--dark-text);font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:260px"><?= Security::e($post['title']) ?></span>
        </nav>
    </div>
</div>

<!-- ── Article ────────────────────────────────────────────── -->
<div style="max-width:860px;margin:0 auto;padding:52px 24px 96px">

    <!-- Category tag -->
    <?php if (!empty($post['category'])): ?>
    <span class="blog-tag" style="margin-bottom:20px;display:inline-block"><?= Security::e($post['category']) ?></span>
    <?php endif; ?>

    <!-- Title -->
    <h1 style="font-size:clamp(1.8rem,4vw,2.8rem);font-weight:800;letter-spacing:-.04em;
               color:var(--dark-text);line-height:1.15;margin:16px 0 20px">
        <?= Security::e($post['title']) ?>
    </h1>

    <!-- Meta -->
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:16px;
                font-size:13px;color:var(--muted);margin-bottom:32px;
                padding-bottom:24px;border-bottom:1.5px solid var(--border)">
        <span style="display:flex;align-items:center;gap:6px">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <strong style="color:var(--dark-text)"><?= Security::e($post['author_name']) ?></strong>
        </span>
        <span style="display:flex;align-items:center;gap:6px">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <?= date('d M Y', strtotime($post['published_at'])) ?>
        </span>
        <span style="display:flex;align-items:center;gap:6px">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <?= number_format((int)$post['view_count']) ?> views
        </span>
    </div>

    <!-- Featured image -->
    <?php if (!empty($post['featured_image'])): ?>
    <div style="margin-bottom:40px;border-radius:16px;overflow:hidden;
                border:1.5px solid var(--border)">
        <img src="<?= Url::image($post['featured_image']) ?>"
             alt="<?= Security::e($post['title']) ?>"
             style="width:100%;max-height:480px;object-fit:cover;display:block">
    </div>
    <?php endif; ?>

    <!-- Excerpt (lead) -->
    <?php if (!empty($post['excerpt'])): ?>
    <p style="font-size:18px;font-weight:500;color:var(--dark-text);line-height:1.7;
              margin-bottom:32px;padding-bottom:32px;border-bottom:1px solid var(--border)">
        <?= Security::e($post['excerpt']) ?>
    </p>
    <?php endif; ?>

    <!-- Content body -->
    <?php if (!empty($post['content'])): ?>
    <div class="blog-post-content">
        <?= $post['content'] ?>
    </div>
    <?php else: ?>
    <p style="color:var(--muted);font-size:15px;font-style:italic">
        No content available for this post yet.
    </p>
    <?php endif; ?>

    <!-- Footer -->
    <div style="margin-top:56px;padding-top:28px;border-top:1.5px solid var(--border);
                display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
        <a href="<?= Url::base('blog') ?>"
           style="display:inline-flex;align-items:center;gap:8px;font-size:14px;
                  font-weight:600;color:var(--c-primary);text-decoration:none;
                  padding:10px 20px;border:1.5px solid var(--border);border-radius:8px;
                  transition:background .15s,border-color .15s">
            ← Back to Blog
        </a>
        <!-- Share -->
        <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em">Share:</span>
            <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' — ' . \App\Helpers\Url::current()) ?>"
               target="_blank" rel="noopener"
               style="padding:8px 14px;border:1.5px solid var(--border);border-radius:6px;
                      font-size:12px;font-weight:600;color:var(--c-primary);text-decoration:none">
                WhatsApp
            </a>
            <button onclick="navigator.clipboard.writeText(window.location.href).then(function(){this.textContent='Copied!';setTimeout(function(){document.querySelector('.copy-btn').textContent='Copy Link'},2000)}.bind(this))"
                    class="copy-btn"
                    style="padding:8px 14px;border:1.5px solid var(--border);border-radius:6px;
                           font-size:12px;font-weight:600;color:var(--c-primary);
                           background:#fff;cursor:pointer">
                Copy Link
            </button>
        </div>
    </div>

</div>

<style>
/* ── Blog post content typography ──────────────────────────── */
.blog-post-content {
    font-size: 16px;
    line-height: 1.85;
    color: var(--dark-text);
}
.blog-post-content h2 {
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -.03em;
    color: var(--dark-text);
    margin: 40px 0 16px;
}
.blog-post-content h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--dark-text);
    margin: 32px 0 12px;
}
.blog-post-content p {
    margin: 0 0 20px;
    color: var(--dark-text);
}
.blog-post-content ul,
.blog-post-content ol {
    margin: 0 0 20px 24px;
    color: var(--dark-text);
}
.blog-post-content li { margin-bottom: 8px; }
.blog-post-content a {
    color: var(--c-primary);
    text-decoration: underline;
}
.blog-post-content blockquote {
    border-left: 3px solid var(--c-primary);
    margin: 28px 0;
    padding: 16px 24px;
    background: var(--off-white);
    border-radius: 0 8px 8px 0;
    font-style: italic;
    color: var(--muted);
}
.blog-post-content img {
    max-width: 100%;
    border-radius: 10px;
    margin: 20px 0;
    border: 1.5px solid var(--border);
}
.blog-post-content hr {
    border: none;
    border-top: 1.5px solid var(--border);
    margin: 36px 0;
}
.blog-post-content strong { color: var(--dark-text); font-weight: 700; }
.blog-post-content code {
    background: var(--off-white);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 14px;
    color: var(--c-primary);
    border: 1px solid var(--border);
}
</style>
