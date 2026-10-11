<?php use App\Helpers\{Url, Security, Str}; ?>

<?php
/* ══ Hero slides data ══════════════════════════════════════
   Slides come from the CMS (Admin → Content → Home Slides).
   Falls back to the original launch slides when the table is
   empty or the migration hasn't run yet. */
$heroSlides = $heroSlides ?? [];
if (empty($heroSlides)) {
    $heroSlides = [
        ['type'=>'image','media_path'=>'https://images.unsplash.com/photo-1560185007-cde436f6a4d0?w=1800&q=80&auto=format&fit=crop','label'=>'Property Information Platform','title'=>"See it.\nUnderstand it.\nThen decide.",'subtitle'=>'Know the dimensions, road access, utilities and neighbourhood before you spend money travelling to inspect.','btn1_text'=>'Browse properties','btn1_url'=>'properties','btn2_text'=>'How it works','btn2_url'=>'how-it-works'],
        ['type'=>'image','media_path'=>'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1800&q=80&auto=format&fit=crop','label'=>'Agency Verified Listings','title'=>"Every property\nphysically inspected\nby our team.",'subtitle'=>'Our agents confirm measurements, photos, utilities and road accessibility before any listing goes live.','btn1_text'=>'View verified listings','btn1_url'=>'properties','btn2_text'=>'','btn2_url'=>''],
        ['type'=>'image','media_path'=>'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1800&q=80&auto=format&fit=crop','label'=>'For Landlords','title'=>"Fewer wasted\nvisits.\nBetter tenants.",'subtitle'=>'Renters who request a viewing have already reviewed your property details. Every visit is a serious one.','btn1_text'=>'List your property','btn1_url'=>'auth/register','btn2_text'=>'Learn more','btn2_url'=>'for-landlords'],
        ['type'=>'image','media_path'=>'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=1800&q=80&auto=format&fit=crop','label'=>'Room Dimensions Included','title'=>"Exact room sizes.\nRoad access times.\nNo surprises.",'subtitle'=>'Every listing includes measured room dimensions, walking time to the main road, vehicle access and utility details.','btn1_text'=>'Start searching','btn1_url'=>'properties','btn2_text'=>'','btn2_url'=>''],
    ];
}
$slideMedia = fn(string $p): string => preg_match('#^https?://#i', $p) ? $p : Url::base($p);
$slideLink  = fn(string $p): string => preg_match('#^https?://#i', $p) ? $p : Url::base($p ?: 'properties');
?>
<!-- ══ HERO SLIDER ════════════════════════════════════════════ -->
<section class="hero2" id="heroSlider">

    <!-- ── Background slides ───────────────────────────────── -->
    <div class="hero2-slides" id="heroSlides">
<?php foreach ($heroSlides as $i => $s): ?>
        <div class="hero2-slide <?= $i === 0 ? 'active' : '' ?>">
<?php if (($s['type'] ?? 'image') === 'video' && !empty($s['media_path'])): ?>
            <video src="<?= Security::e($slideMedia((string)$s['media_path'])) ?>" muted loop playsinline preload="<?= $i===0?'auto':'metadata' ?>"<?= $i===0?' autoplay':'' ?>></video>
<?php elseif (!empty($s['media_path'])): ?>
            <img src="<?= Security::e($slideMedia((string)$s['media_path'])) ?>" alt="" loading="<?= $i===0?'eager':'lazy' ?>">
<?php endif; ?>
        </div>
<?php endforeach; ?>
    </div>

    <!-- ── Dark overlay ────────────────────────────────────── -->
    <div class="hero2-overlay"></div>

    <!-- ── Main content layer ──────────────────────────────── -->
    <div class="hero2-body">

        <!-- LEFT — headline + CTA -->
        <div class="hero2-left">

            <div class="hero2-slide-meta" id="heroSlideLabel">
                <span class="hero2-label"><?= Security::e($heroSlides[0]['label'] ?? 'Property Information Platform') ?></span>
            </div>

            <h1 class="hero2-title" id="heroTitle">
                <?= nl2br(Security::e($heroSlides[0]['title'] ?? "See it.\nUnderstand it.\nThen decide.")) ?>
            </h1>

            <p class="hero2-sub" id="heroSub">
                <?= Security::e($heroSlides[0]['subtitle'] ?? 'Know the dimensions, road access, utilities and neighbourhood before you spend money travelling to inspect.') ?>
            </p>

            <div class="hero2-cta">
                <a href="<?= Url::base('properties') ?>" class="hero2-btn-primary">
                    Get started
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <a href="<?= Url::base('how-it-works') ?>" class="hero2-btn-ghost">How it works</a>
            </div>

            <!-- Bottom-left: avatars + user count -->
            <div class="hero2-social-proof">
                <div class="hero2-avatars">
                    <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=80&q=80&auto=format&fit=crop&crop=faces" alt="">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80&q=80&auto=format&fit=crop&crop=faces" alt="">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&q=80&auto=format&fit=crop&crop=faces" alt="">
                    <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?w=80&q=80&auto=format&fit=crop&crop=faces" alt="">
                </div>
                <div class="hero2-social-text">
                    <strong><?= number_format($stats['viewings'] + 1000) ?>+</strong>
                    <span>renters found homes</span>
                </div>
            </div>
        </div>

        <!-- Prev / Next buttons — bottom-right of hero -->
        <div class="hero2-nav">
            <button class="hero2-nav-btn" id="heroPrev" aria-label="Previous">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button class="hero2-nav-btn" id="heroNext" aria-label="Next">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>

    <!-- ── Progress bar ────────────────────────────────────── -->
    <div class="hero2-progress" id="heroProgress"></div>

</section>

<!-- ── Hero search bar ─────────────────────────────────────── -->
<form class="hero-search" id="heroSearch" method="GET" action="<?= Url::base('properties') ?>">
    <input type="text" name="q" class="hero-search-input"
           placeholder="Search by area, street or keyword..."
           value="<?= Security::e($_GET['q'] ?? '') ?>">
    <div class="hero-search-divider hero-search-desktop"></div>
    <select name="region_id" class="hero-search-select hero-search-desktop">
        <option value="">All Regions</option>
        <?php foreach ($regions as $r): ?>
        <option value="<?= $r['id'] ?>"><?= Security::e($r['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <div class="hero-search-divider hero-search-desktop"></div>
    <select name="type_id" class="hero-search-select hero-search-desktop">
        <option value="">Property type</option>
        <?php foreach ($types as $t): ?>
        <option value="<?= $t['id'] ?>"><?= Security::e($t['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="hero-search-btn">Search</button>
</form>

<!-- ══ TICKER ════════════════════════════════════════════════ -->
<div class="ticker-wrap">
    <div class="ticker-inner">
        <?php for ($i = 0; $i < 3; $i++): ?>
        <span class="ticker-item">Verified Properties<span>&#47;&#47;</span></span>
        <span class="ticker-item">Room Dimensions Included<span>&#47;&#47;</span></span>
        <span class="ticker-item">Road Accessibility Data<span>&#47;&#47;</span></span>
        <span class="ticker-item">Agency Verified<span>&#47;&#47;</span></span>
        <span class="ticker-item">Physical Inspection<span>&#47;&#47;</span></span>
        <span class="ticker-item">Know Before You Go<span>&#47;&#47;</span></span>
        <span class="ticker-item">Serious Viewings Only<span>&#47;&#47;</span></span>
        <span class="ticker-item">Ghana&rsquo;s Property Platform<span>&#47;&#47;</span></span>
        <?php endfor; ?>
    </div>
</div>

<!-- ══ STATS ═════════════════════════════════════════════════ -->
<div class="stats-strip">
    <div class="home-section-inner stats-inner">
        <div class="stat-item">
            <div class="stat-number"><?= number_format($stats['verified']) ?>+</div>
            <div class="stat-label">Verified listings</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="stat-number"><?= number_format($stats['landlords']) ?>+</div>
            <div class="stat-label">Landlords</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="stat-number"><?= number_format($stats['viewings']) ?>+</div>
            <div class="stat-label">Viewings completed</div>
        </div>
    </div>
</div>

<!-- ══ FEATURED PROPERTIES ════════════════════════════════════ -->
<?php if (!empty($featuredProperties)): ?>
<section class="section">
    <div class="home-section-inner">
        <div class="section-row-header">
            <div>
                <span class="label">Featured</span>
                <h2 style="margin-bottom:0;color:#051F20">Hand-picked properties</h2>
            </div>
            <a href="<?= Url::base('properties?featured=1') ?>" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div class="pcard-grid" style="margin-top:8px">
            <?php foreach ($featuredProperties as $p): ?>
            <?php include ROOT_PATH . '/app/Views/partials/property_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══ WHY CHOOSE US ══════════════════════════════════════════ -->
<section class="section-alt">
    <div class="home-section-inner">
        <div class="section-header centered">
            <span class="label">Our difference</span>
            <h2 style="color:#051F20">Not just listings.<br>A complete property profile.</h2>
            <p style="color:#5a7a6e">We built the information renters actually need to make a decision — before they travel.</p>
        </div>
        <div class="feature-grid">
            <?php
            $features = [
                ['n'=>'01','title'=>'Verified Room Dimensions','body'=>'Every major room is measured and confirmed by our team. Bedroom, kitchen, bathroom — you see exact Length × Width × Height before visiting.'],
                ['n'=>'02','title'=>'Road Accessibility Data','body'=>'Walking minutes to the main road, vehicle access, road surface condition, public transport availability. No more arriving to discover a 30-minute walk on a dirt road.'],
                ['n'=>'03','title'=>'Utility Information','body'=>'Meter type (self, prepaid, shared), water source, availability frequency and storage. You know exactly what you are paying for before signing anything.'],
                ['n'=>'04','title'=>'Physical Verification','body'=>'Our agents physically inspect every property. Photos, dimensions, facilities and location are confirmed before the listing goes live.'],
                ['n'=>'05','title'=>'Side-by-Side Comparison','body'=>'Add up to four properties and compare every detail in one table — rent, rooms, parking, road access, ceiling type, flooring and more.'],
                ['n'=>'06','title'=>'Qualified Viewings Only','body'=>'Renters who book a viewing have already reviewed the full property profile. Every physical visit is a serious one, reducing disturbance to current occupants.'],
            ];
            foreach ($features as $f): ?>
            <div class="feature-block">
                <div class="feature-block-num"><?= $f['n'] ?></div>
                <h3 style="color:#051F20"><?= $f['title'] ?></h3>
                <p style="color:#5a7a6e"><?= $f['body'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ BROWSE BY TYPE ═════════════════════════════════════════ -->
<section class="section">
    <div class="home-section-inner">
        <div class="section-header">
            <span class="label">Categories</span>
            <h2 style="color:#051F20">Browse by property type</h2>
        </div>
        <div class="type-grid">
            <?php foreach ($types as $t):
                $count = (int)\App\Helpers\Database::fetchColumn(
                    "SELECT COUNT(*) FROM properties p
                     WHERE p.property_type_id = ? AND p.status='verified' AND p.deleted_at IS NULL",
                    [$t['id']]
                );
            ?>
            <a href="<?= Url::base('properties?type_id=' . $t['id']) ?>" class="type-tile">
                <div class="type-tile-num"><?= $count ?></div>
                <div class="type-tile-name"><?= Security::e($t['name']) ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ RECENT PROPERTIES ══════════════════════════════════════ -->
<section class="section-alt">
    <div class="home-section-inner">
        <div class="section-row-header">
            <div>
                <span class="label">Latest</span>
                <h2 style="margin-bottom:0;color:#051F20">Recently verified</h2>
            </div>
            <a href="<?= Url::base('properties') ?>" class="btn btn-secondary btn-sm">All properties</a>
        </div>
        <div class="pcard-grid">
            <?php foreach ($recentProperties as $p): ?>
            <?php include ROOT_PATH . '/app/Views/partials/property_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ HOW IT WORKS ═══════════════════════════════════════════ -->
<section class="section">
    <div class="home-section-inner home-howitworks-grid">
        <div class="home-howitworks-sticky">
            <span class="label">Process</span>
            <h2 style="margin-bottom:16px;color:#051F20">Find a place<br>without wasted trips.</h2>
            <p style="font-size:15px;color:#5a7a6e;line-height:1.7;margin-bottom:32px">Every step is designed to give you more information and fewer unnecessary visits to properties that don't suit you.</p>
            <a href="<?= Url::base('auth/register') ?>" class="btn btn-primary">Start for free</a>
        </div>
        <div class="steps-list">
            <?php
            $steps = [
                ['n'=>'Step 01','title'=>'Search','body'=>'Filter by location, price range, number of bedrooms, road accessibility, parking, water, electricity type, ceiling, flooring and over 20 other criteria.'],
                ['n'=>'Step 02','title'=>'Compare','body'=>'Add properties to your comparison list and view them side by side. See every spec — rent, dimensions, utilities, access time — in one table.'],
                ['n'=>'Step 03','title'=>'Review the full profile','body'=>'Read the complete property profile: verified photos, exact room dimensions, road access data, utility details and landlord information.'],
                ['n'=>'Step 04','title'=>'Request a viewing','body'=>'Still want to see it in person? Pay a small viewing fee to book a supervised physical visit. Our agent meets you at the property.'],
                ['n'=>'Step 05','title'=>'Move in','body'=>'Agree terms with the landlord. Sign the agreement and move in. The whole process — from search to keys — is handled on the platform.'],
            ];
            foreach ($steps as $s): ?>
            <div class="step-item">
                <div class="step-num"><?= $s['n'] ?></div>
                <div class="step-content">
                    <h3 style="color:#051F20"><?= $s['title'] ?></h3>
                    <p style="color:#5a7a6e"><?= $s['body'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ PROPERTY SNAPSHOT ══════════════════════════════════════ -->
<section class="section-dark">
    <div class="home-section-inner home-snapshot-grid">
        <div>
            <span class="label" style="color:var(--c-sage)">Example Listing</span>
            <h2 style="color:#DAF1DE;margin-bottom:16px">This is what renters see<br>before visiting.</h2>
            <p style="font-size:15px;color:var(--c-sage);line-height:1.7;margin-bottom:32px">Every verified listing comes with a complete information profile so you can make a real decision before spending money on transport.</p>
            <a href="<?= Url::base('properties') ?>" class="btn btn-primary" style="background:#fff;color:#051F20;border-color:#fff">Browse listings</a>
        </div>
        <div class="snapshot-card">
            <div class="snapshot-card-header">
                <div>
                    <div style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#5a7a6e;margin-bottom:4px">1 Bedroom Self-Contained — Takoradi</div>
                    <div style="font-size:22px;font-weight:800;color:#051F20;letter-spacing:-.03em">GH&#8373;1,800<span style="font-size:13px;font-weight:500;color:#5a7a6e">/month</span></div>
                </div>
                <span style="background:#235347;color:#fff;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding:5px 12px;border-radius:4px;flex-shrink:0">Verified</span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px">
                <?php foreach (['1 Bed','Private Bathroom','Parking','24/7 Water','Prepaid Meter','Gated','Self-Contained'] as $pill): ?>
                <span style="font-size:12px;font-weight:600;border:1.5px solid #d6ede0;padding:4px 12px;border-radius:20px;color:#235347;background:#DAF1DE"><?= $pill ?></span>
                <?php endforeach; ?>
            </div>
            <div style="height:1px;background:#d6ede0;margin-bottom:20px"></div>
            <div class="snapshot-stats-grid">
                <div class="snapshot-stat">
                    <div style="font-size:20px;font-weight:800;letter-spacing:-.03em;color:#051F20">7 <span style="font-size:12px;font-weight:500;color:#5a7a6e">min</span></div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#5a7a6e;margin-top:2px">Walk</div>
                </div>
                <div class="snapshot-stat snapshot-stat--accent">
                    <div style="font-size:20px;font-weight:800;letter-spacing:-.03em;color:#fff">Yes</div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:rgba(218,241,222,.7);margin-top:2px">Car Access</div>
                </div>
                <div class="snapshot-stat">
                    <div style="font-size:20px;font-weight:800;letter-spacing:-.03em;color:#051F20">0.4 <span style="font-size:12px;font-weight:500;color:#5a7a6e">km</span></div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#5a7a6e;margin-top:2px">Main Road</div>
                </div>
            </div>
            <div class="snapshot-dims-grid">
                <?php foreach ([['Bedroom','4.2×3.5×2.8m'],['Living Room','5.4×4.1×2.9m'],['Kitchen','3.0×2.4×2.8m']] as $d): ?>
                <div class="snapshot-dim">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#5a7a6e;margin-bottom:4px"><?= $d[0] ?></div>
                    <div style="font-size:13px;font-weight:700;color:#235347;letter-spacing:-.01em"><?= $d[1] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ══ TESTIMONIALS ═══════════════════════════════════════════ -->
<section class="section">
    <div class="home-section-inner">
        <div class="section-header centered">
            <span class="label">Testimonials</span>
            <h2 style="color:#051F20">What people are saying</h2>
        </div>
        <div class="testimonials-grid">
            <?php
            $testimonials = [
                ['text'=>'I saved three trips just by reading the property profile properly. The road access data alone stopped me visiting a place that took 25 minutes to reach on foot from the main road.','name'=>'Abena K.','role'=>'Renter, Takoradi','init'=>'A'],
                ['text'=>'Since listing on RentalWebsite, the people who come to view are serious. They already know the dimensions, the utilities, the location. We don\'t get random visitors anymore.','name'=>'Mr. Asante','role'=>'Landlord, Kumasi','init'=>'A'],
                ['text'=>'The comparison table is what sold it for me. I compared three properties side by side and made my decision without leaving my house. Visited once, moved in.','name'=>'Kwame D.','role'=>'Renter, Accra','init'=>'K'],
            ];
            foreach ($testimonials as $t): ?>
            <div class="testimonial-card">
                <div class="testimonial-quote">&ldquo;</div>
                <p class="testimonial-text" style="color:#051F20"><?= Security::e($t['text']) ?></p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar"><?= $t['init'] ?></div>
                    <div>
                        <div class="testimonial-name" style="color:#051F20"><?= Security::e($t['name']) ?></div>
                        <div class="testimonial-role" style="color:#5a7a6e"><?= Security::e($t['role']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ FOR LANDLORDS ══════════════════════════════════════════ -->
<section class="section-alt">
    <div class="home-section-inner home-landlord-grid">
        <div class="landlord-photos">
            <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80&auto=format&fit=crop" alt="Property" class="landlord-photo-main">
            <div class="landlord-photo-col">
                <img src="https://images.unsplash.com/photo-1523217582562-09d0def993a6?w=600&q=80&auto=format&fit=crop" alt="Property">
                <img src="https://images.unsplash.com/photo-1484154218962-a197022b5858?w=600&q=80&auto=format&fit=crop" alt="Property">
            </div>
        </div>
        <div>
            <span class="label">For Landlords</span>
            <h2 style="margin-bottom:16px;color:#051F20">Your property,<br>presented properly.</h2>
            <p style="font-size:15px;color:#5a7a6e;line-height:1.7;margin-bottom:28px">We present your property with professional photography, exact room measurements, accessibility information and a full amenity profile — so renters arrive prepared, not curious.</p>
            <div class="landlord-points">
                <?php foreach (['Professional property presentation with verified data','Fewer unnecessary visits from unqualified enquirers','Renters have already reviewed your property before calling','Configurable viewing fee — your time is compensated','Full control over availability and viewing slots'] as $i => $pt): ?>
                <div class="landlord-point">
                    <div class="landlord-point-num"><?= $i + 1 ?></div>
                    <p><?= Security::e($pt) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
                <a href="<?= Url::base('auth/register') ?>" class="btn btn-primary">List a property</a>
                <a href="<?= Url::base('for-landlords') ?>" class="btn btn-secondary">Learn more</a>
            </div>
        </div>
    </div>
</section>

<!-- ══ CTA BAND ═══════════════════════════════════════════════ -->
<div class="cta-band">
    <div class="cta-band-inner">
        <h2 style="color:#DAF1DE">Start your search today.</h2>
        <p style="color:#8EB69B">Hundreds of verified properties with full room dimensions, road access data and utility information.</p>
        <div class="cta-band-btns">
            <a href="<?= Url::base('properties') ?>" class="btn btn-primary btn-lg" style="background:#DAF1DE;color:#051F20;border-color:#DAF1DE">Browse properties</a>
            <a href="<?= Url::base('auth/register') ?>" class="btn btn-lg" style="background:rgba(255,255,255,.08);color:#DAF1DE;border:1.5px solid rgba(218,241,222,.25)">Create free account</a>
        </div>
    </div>
</div>

<!-- ══ HERO SLIDER JS ═════════════════════════════════════════ -->
<script>
(function () {
    var slides   = document.querySelectorAll('.hero2-slide');
    var progress = document.getElementById('heroProgress');
    var track    = document.getElementById('heroSlides');
    var titleEl  = document.getElementById('heroTitle');
    var subEl    = document.getElementById('heroSub');
    var labelEl  = document.getElementById('heroSlideLabel');

    var current  = 0, total = slides.length, duration = 7000;
    var progStart = null, progFrame = null, isPaused = false;

    // Slide data mirrors PHP $heroSlides array
    var slideData = <?= json_encode(array_map(function($s) use ($slideMedia) {
        return [
            'label'    => $s['label'] ?? '',
            'title'    => str_replace("\n", "\n", $s['title'] ?? ''),
            'subtitle' => $s['subtitle'] ?? '',
            'img'      => $slideMedia((string)($s['media_path'] ?? '')),
            'type'     => $s['label'] ?? 'Featured',
        ];
    }, $heroSlides), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;

    function goTo(idx) {
        slides[current].classList.remove('active');
        current = (idx + total) % total;
        slides[current].classList.add('active');
        track.style.transform = 'translateX(-' + (current * 100) + '%)';

        var d = slideData[current];

        // Fade out → update → fade in
        [titleEl, subEl].forEach(function (el) {
            if (el) { el.style.opacity = '0'; el.style.transform = 'translateY(16px)'; }
        });
        setTimeout(function () {
            if (titleEl) titleEl.innerHTML = (d.title || '').replace(/\n/g, '<br>');
            if (subEl)   subEl.textContent  = d.subtitle || '';
            if (labelEl) {
                var span = labelEl.querySelector('.hero2-label');
                if (span) span.textContent = d.label || '';
            }
            [titleEl, subEl].forEach(function (el) {
                if (el) { el.style.opacity = '1'; el.style.transform = 'none'; }
            });
        }, 220);

        syncVideos();
        resetProgress();
    }

    function syncVideos() {
        slides.forEach(function (sl, i) {
            var v = sl.querySelector('video');
            if (!v) return;
            if (i === current) { var p = v.play(); if (p && p.catch) p.catch(function(){}); }
            else v.pause();
        });
    }

    function resetProgress() {
        cancelAnimationFrame(progFrame);
        if (progress) { progress.style.transition = 'none'; progress.style.width = '0%'; }
        progStart = null;
        if (!isPaused) startProgress();
    }

    function startProgress() {
        progStart = performance.now();
        (function frame(ts) {
            var pct = Math.min((ts - progStart) / duration * 100, 100);
            if (progress) progress.style.width = pct + '%';
            if (pct < 100) { progFrame = requestAnimationFrame(frame); }
            else goTo(current + 1);
        })(performance.now());
    }

    document.getElementById('heroPrev')?.addEventListener('click', function () { goTo(current - 1); });
    document.getElementById('heroNext')?.addEventListener('click', function () { goTo(current + 1); });

    var hero = document.getElementById('heroSlider');
    if (hero) {
        hero.addEventListener('mouseenter', function () { isPaused = true; cancelAnimationFrame(progFrame); });
        hero.addEventListener('mouseleave', function () { isPaused = false; resetProgress(); });
        var tx = 0;
        hero.addEventListener('touchstart', function (e) { tx = e.changedTouches[0].screenX; }, { passive: true });
        hero.addEventListener('touchend',   function (e) { var dx = tx - e.changedTouches[0].screenX; if (Math.abs(dx) > 50) goTo(dx > 0 ? current + 1 : current - 1); }, { passive: true });
    }

    // Transition styles on text elements
    [titleEl, subEl].forEach(function (el) {
        if (el) el.style.transition = 'opacity .3s ease, transform .3s ease';
    });

    startProgress();
    syncVideos();
})();
</script>
