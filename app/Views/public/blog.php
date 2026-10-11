<?php use App\Helpers\{Url, Security, Str}; ?>

<!-- ── Blog Header ─────────────────────────────────────── -->
<div class="blog-header">
    <div class="blog-header__inner">
        <p class="blog-header__eyebrow">Blog &amp; Guides</p>
        <h1 class="blog-header__title">Advice for renters<br>and landlords in Ghana.</h1>
    </div>
</div>

<?php if (empty($posts)): ?>
<!-- ── Empty state ─────────────────────────────────────── -->
<div class="container" style="padding:80px 24px;text-align:center">
    <div style="font-size:40px;margin-bottom:16px;opacity:.25">&#128240;</div>
    <p style="color:var(--muted);font-size:16px">No posts published yet. Check back soon.</p>
</div>

<?php else:
    $featured = $posts[0];
    $rest     = array_slice($posts, 1);
?>

<!-- ── Featured post ───────────────────────────────────── -->
<div class="container blog-featured-wrap">
    <a href="<?= Url::base('blog/' . $featured['slug']) ?>" class="blog-featured">

        <div class="blog-featured__img">
            <?php if (!empty($featured['featured_image'])): ?>
            <img src="<?= Url::image($featured['featured_image']) ?>"
                 alt="<?= Security::e($featured['title']) ?>"
                 loading="eager">
            <?php else: ?>
            <div class="blog-featured__img-placeholder"></div>
            <?php endif; ?>
        </div>

        <div class="blog-featured__body">
            <div class="blog-featured__meta">
                <?php if (!empty($featured['category'])): ?>
                <span class="blog-tag"><?= Security::e($featured['category']) ?></span>
                <?php endif; ?>
                <span class="blog-featured__date">
                    <?= date('d M Y', strtotime($featured['published_at'])) ?>
                </span>
            </div>

            <h2 class="blog-featured__title">
                <?= Security::e($featured['title']) ?>
            </h2>

            <?php if (!empty($featured['excerpt'])): ?>
            <p class="blog-featured__excerpt">
                <?= Security::e(Str::truncate($featured['excerpt'], 180)) ?>
            </p>
            <?php endif; ?>

            <div class="blog-featured__footer">
                <span class="blog-featured__author">By <?= Security::e($featured['author_name']) ?></span>
                <span class="blog-read-more">Read article &rarr;</span>
            </div>
        </div>

    </a>
</div>

<?php if (!empty($rest)): ?>
<!-- ── Post grid ───────────────────────────────────────── -->
<div class="container blog-grid-wrap">
    <?php if (count($rest) > 1): ?>
    <h2 class="blog-section-title">More articles</h2>
    <?php endif; ?>

    <div class="blog-grid">
        <?php foreach ($rest as $post): ?>
        <article class="blog-card">

            <a href="<?= Url::base('blog/' . $post['slug']) ?>" class="blog-card__img-link" tabindex="-1">
                <div class="blog-card__img">
                    <?php if (!empty($post['featured_image'])): ?>
                    <img src="<?= Url::image($post['featured_image']) ?>"
                         alt="<?= Security::e($post['title']) ?>"
                         loading="lazy">
                    <?php else: ?>
                    <div class="blog-card__img-placeholder"></div>
                    <?php endif; ?>
                </div>
            </a>

            <div class="blog-card__body">
                <div class="blog-card__meta">
                    <?php if (!empty($post['category'])): ?>
                    <span class="blog-tag blog-tag--sm"><?= Security::e($post['category']) ?></span>
                    <?php endif; ?>
                    <span class="blog-card__date">
                        <?= date('d M Y', strtotime($post['published_at'])) ?>
                    </span>
                </div>

                <a href="<?= Url::base('blog/' . $post['slug']) ?>" class="blog-card__title">
                    <?= Security::e(Str::truncate($post['title'], 72)) ?>
                </a>

                <?php if (!empty($post['excerpt'])): ?>
                <p class="blog-card__excerpt">
                    <?= Security::e(Str::truncate($post['excerpt'], 110)) ?>
                </p>
                <?php endif; ?>

                <div class="blog-card__footer">
                    <span class="blog-card__author"><?= Security::e($post['author_name']) ?></span>
                    <a href="<?= Url::base('blog/' . $post['slug']) ?>" class="blog-card__cta">Read &rarr;</a>
                </div>
            </div>

        </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>
