<?php use App\Helpers\{Url, Security}; ?>

<!-- ══ HERO ══════════════════════════════════════════════════ -->
<div style="background:var(--c-darkest);padding:80px 0 72px">
    <div class="home-section-inner" style="text-align:center">
        <p style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--c-sage);margin-bottom:16px">For Renters</p>
        <h1 style="font-size:clamp(2rem,4.5vw,3.2rem);font-weight:800;color:#fff;letter-spacing:-.04em;line-height:1.1;margin-bottom:18px">
            See it. Understand it.<br>Then decide.
        </h1>
        <p style="font-size:17px;color:rgba(218,241,222,.75);max-width:560px;margin:0 auto 36px;line-height:1.7">
            Every listing includes the information you actually need — room sizes, road access, utilities and verified photos — before you spend money travelling to inspect.
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            <a href="<?= Url::base('properties') ?>" class="btn btn-primary btn-lg">Search properties</a>
            <a href="<?= Url::base('auth/register') ?>" class="btn btn-lg" style="background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.25)">Create free account</a>
        </div>
    </div>
</div>

<!-- ══ PAIN POINTS ════════════════════════════════════════════ -->
<section class="section-alt">
    <div class="home-section-inner">
        <div style="text-align:center;margin-bottom:48px">
            <span class="label">The problem we solve</span>
            <h2 style="color:var(--dark-text)">Renting in Ghana is broken.<br>We're fixing it.</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px">
            <?php
            $pains = [
                ['before'=>'You travel 2 hours to see a property only to discover it\'s a single room marketed as a 2-bedroom.','after'=>'Every listing includes verified room dimensions confirmed by our agent on-site.'],
                ['before'=>'The road to the property is a 25-minute dirt track — never mentioned in the listing.','after'=>'Every listing includes walking time to the main road, road surface condition and vehicle accessibility.'],
                ['before'=>'You arrive to find no running water despite the listing saying "water available".','after'=>'Water source, availability frequency and meter type are verified and disclosed on every listing.'],
                ['before'=>'Photos show a furnished, freshly painted room that bears no resemblance to reality.','after'=>'All photos are confirmed during the physical inspection before the listing is published.'],
            ];
            foreach ($pains as $p): ?>
            <div style="background:#fff;border:1.5px solid var(--border);border-radius:14px;overflow:hidden">
                <div style="background:#fef2f2;padding:18px 20px;border-bottom:1.5px solid #fecaca">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#991b1b;margin-bottom:8px">The old way</div>
                    <p style="font-size:14px;color:#7f1d1d;line-height:1.65;margin:0"><?= $p['before'] ?></p>
                </div>
                <div style="padding:18px 20px">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#166534;margin-bottom:8px">With RentalWebsite</div>
                    <p style="font-size:14px;color:var(--dark-text);line-height:1.65;margin:0"><?= $p['after'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ WHAT'S IN EVERY LISTING ════════════════════════════════ -->
<section class="section">
    <div class="home-section-inner">
        <div style="text-align:center;margin-bottom:48px">
            <span class="label">Every verified listing includes</span>
            <h2 style="color:var(--dark-text)">More information than any<br>other property platform.</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px">
            <?php
            $items = [
                ['title'=>'Room Dimensions','body'=>'Exact L×W×H measurements for every major room — bedroom, kitchen, bathroom, living room.'],
                ['title'=>'Road Accessibility','body'=>'Walk time to main road, vehicle access, road surface (tarred/gravel/dirt) and public transport links.'],
                ['title'=>'Electricity Details','body'=>'Meter type: self meter, shared meter, prepaid or postpaid. Confirmed on-site.'],
                ['title'=>'Water Information','body'=>'Water source (GWCL, borehole, tank), availability and frequency of supply.'],
                ['title'=>'Security Features','body'=>'Gated compound, security guard, CCTV, fenced, street lighting and intercom where present.'],
                ['title'=>'Verified Photos','body'=>'Photos confirmed during physical inspection to match the actual property.'],
                ['title'=>'Landlord Identity','body'=>'Landlord ID verified during inspection. You know exactly who owns the property.'],
                ['title'=>'Availability & Lease','body'=>'Available from date, minimum lease period, security deposit and agency fee — all disclosed upfront.'],
            ];
            foreach ($items as $it): ?>
            <div style="background:var(--off-white);border:1.5px solid var(--border);border-radius:12px;padding:20px">
                <div style="width:32px;height:32px;background:var(--c-primary);border-radius:8px;
                            display:flex;align-items:center;justify-content:center;
                            color:#fff;font-size:14px;font-weight:800;margin-bottom:12px">&#10003;</div>
                <div style="font-size:14px;font-weight:700;color:var(--dark-text);margin-bottom:6px"><?= $it['title'] ?></div>
                <div style="font-size:13px;color:var(--muted);line-height:1.6"><?= $it['body'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ HOW TO USE ═════════════════════════════════════════════ -->
<section class="section-alt">
    <div class="home-section-inner">
        <div style="text-align:center;margin-bottom:48px">
            <span class="label">Getting started</span>
            <h2 style="color:var(--dark-text)">Three steps to finding<br>your next home.</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:28px">
            <?php
            $steps = [
                ['n'=>'1','title'=>'Search & filter','body'=>'Use our detailed filters to narrow down exactly what you need — location, price, bedrooms, road access, water, electricity, ceiling type and more.','link'=>Url::base('properties'),'cta'=>'Search now'],
                ['n'=>'2','title'=>'Review the full profile','body'=>'Read the complete property profile including verified room dimensions, road access data, utility details, security features and confirmed photos.','link'=>Url::base('how-it-works'),'cta'=>'How it works'],
                ['n'=>'3','title'=>'Book a viewing','body'=>'Pay the viewing fee to book a supervised physical inspection. Our agent meets you at the property. Everything you see online will match what you see in person.','link'=>Url::base('auth/register'),'cta'=>'Get started'],
            ];
            foreach ($steps as $s): ?>
            <div style="background:#fff;border:1.5px solid var(--border);border-radius:14px;padding:28px">
                <div style="width:40px;height:40px;background:var(--c-darkest);border-radius:10px;
                            display:flex;align-items:center;justify-content:center;
                            color:#DAF1DE;font-size:18px;font-weight:800;margin-bottom:20px"><?= $s['n'] ?></div>
                <h3 style="font-size:17px;font-weight:700;color:var(--dark-text);margin-bottom:10px;letter-spacing:-.02em"><?= $s['title'] ?></h3>
                <p style="font-size:14px;color:var(--muted);line-height:1.7;margin-bottom:20px"><?= $s['body'] ?></p>
                <a href="<?= $s['link'] ?>" style="font-size:13px;font-weight:700;color:var(--c-primary);text-decoration:none"><?= $s['cta'] ?> &rarr;</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ TESTIMONIALS ═══════════════════════════════════════════ -->
<section class="section">
    <div class="home-section-inner">
        <div style="text-align:center;margin-bottom:40px">
            <span class="label">Real renters</span>
            <h2 style="color:var(--dark-text)">What renters are saying</h2>
        </div>
        <div class="testimonials-grid">
            <?php
            $testimonials = [
                ['text'=>'I saved three trips just by reading the property profile. The road access data alone stopped me visiting a place that took 25 minutes to reach on foot from the main road.','name'=>'Abena K.','role'=>'Renter, Takoradi','init'=>'A'],
                ['text'=>'The comparison table is what sold it for me. I compared three properties side by side and made my decision without leaving my house. Visited once, moved in the same week.','name'=>'Kwame D.','role'=>'Renter, Accra','init'=>'K'],
                ['text'=>'Finally a platform that shows you the real dimensions. I knew the bedroom was 3.8×3.2m before I even booked a viewing. No surprises when I arrived.','name'=>'Ama T.','role'=>'Renter, Kumasi','init'=>'A'],
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

<!-- ══ CTA ════════════════════════════════════════════════════ -->
<div class="cta-band">
    <div class="cta-band-inner">
        <h2 style="color:#DAF1DE">Start searching for free.</h2>
        <p style="color:#8EB69B">Hundreds of verified properties with full room dimensions, road access data and utility information. No credit card needed.</p>
        <div class="cta-band-btns">
            <a href="<?= Url::base('properties') ?>" class="btn btn-primary btn-lg" style="background:#DAF1DE;color:#051F20;border-color:#DAF1DE">Search properties</a>
            <a href="<?= Url::base('auth/register') ?>" class="btn btn-lg" style="background:rgba(255,255,255,.08);color:#DAF1DE;border:1.5px solid rgba(218,241,222,.25)">Register free</a>
        </div>
    </div>
</div>
