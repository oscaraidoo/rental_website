<?php use App\Helpers\{Url, Security}; ?>

<!-- ══ HERO ══════════════════════════════════════════════════ -->
<div style="background:var(--c-darkest);padding:80px 0 72px">
    <div class="home-section-inner" style="text-align:center">
        <p style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--c-sage);margin-bottom:16px">How It Works</p>
        <h1 style="font-size:clamp(2rem,4.5vw,3.2rem);font-weight:800;color:#fff;letter-spacing:-.04em;line-height:1.1;margin-bottom:18px">
            Find a home without<br>wasted trips.
        </h1>
        <p style="font-size:17px;color:rgba(218,241,222,.75);max-width:540px;margin:0 auto 36px;line-height:1.7">
            RentalWebsite gives you everything you need to make a decision before you ever leave your house.
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            <a href="<?= Url::base('properties') ?>" class="btn btn-primary btn-lg">Browse properties</a>
            <a href="<?= Url::base('auth/register') ?>" class="btn btn-lg" style="background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.25)">Create free account</a>
        </div>
    </div>
</div>

<!-- ══ FOR RENTERS — STEPS ════════════════════════════════════ -->
<section class="section">
    <div class="home-section-inner">
        <div style="text-align:center;margin-bottom:56px">
            <span class="label">For Renters</span>
            <h2 style="color:var(--dark-text);margin-bottom:12px">From search to keys — all on one platform</h2>
            <p style="color:var(--muted);font-size:16px;max-width:520px;margin:0 auto">Follow these steps to find, evaluate and move into your next home without unnecessary travel.</p>
        </div>

        <div class="hiw-steps">
            <?php
            $renterSteps = [
                ['n'=>'01','title'=>'Create a free account','body'=>'Register in under a minute. No credit card needed. Your account lets you save properties, compare listings side by side and request viewings.','cta'=>null],
                ['n'=>'02','title'=>'Search with detailed filters','body'=>'Filter by location, price, number of bedrooms, road accessibility, water, electricity type, ceiling type, flooring and over 20 other criteria — so you only see properties that actually fit your needs.','cta'=>null],
                ['n'=>'03','title'=>'Read the full property profile','body'=>'Every verified listing includes exact room dimensions (L×W×H), walking time to the main road, vehicle access status, utility details, security features and verified photos — not just a headline and a price.','cta'=>null],
                ['n'=>'04','title'=>'Compare properties side by side','body'=>'Add up to 4 properties to your comparison list and view every detail in a single table. Rent, rooms, parking, road access, ceiling type, flooring — all side by side.','cta'=>null],
                ['n'=>'05','title'=>'Save your favourites','body'=>'Heart any property to save it to your list. Come back later, compare saved properties or share the link with your family before making a decision.','cta'=>null],
                ['n'=>'06','title'=>'Request a supervised viewing','body'=>'Pay a small viewing fee to book a physical inspection. Our agent meets you at the property and ensures the visit matches the online listing exactly. No surprises.','cta'=>null],
                ['n'=>'07','title'=>'Move in','body'=>'Agree terms with the landlord through the platform, sign the digital agreement, and move in. The entire process — from search to keys — is handled in one place.','cta'=>null],
            ];
            foreach ($renterSteps as $i => $s): ?>
            <div class="hiw-step">
                <div class="hiw-step__num"><?= $s['n'] ?></div>
                <div class="hiw-step__body">
                    <h3 class="hiw-step__title"><?= $s['title'] ?></h3>
                    <p class="hiw-step__text"><?= $s['body'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ FOR LANDLORDS — STEPS ══════════════════════════════════ -->
<section class="section-alt">
    <div class="home-section-inner">
        <div style="text-align:center;margin-bottom:56px">
            <span class="label">For Landlords</span>
            <h2 style="color:var(--dark-text);margin-bottom:12px">List your property. Attract serious renters.</h2>
            <p style="color:var(--muted);font-size:16px;max-width:520px;margin:0 auto">Every renter who books a viewing has already reviewed your full property profile. No more wasted visits.</p>
        </div>

        <div class="hiw-steps">
            <?php
            $landlordSteps = [
                ['n'=>'01','title'=>'Register and create your profile','body'=>'Sign up as a landlord and complete your profile. Verified landlords get a trust badge that helps renters feel confident about your listing.'],
                ['n'=>'02','title'=>'List your property with full details','body'=>'Add your property with all the details renters actually want: room dimensions, road access time, utility types, security features and high-quality photos. The more complete your listing, the more serious enquiries you receive.'],
                ['n'=>'03','title'=>'Our team verifies the listing','body'=>'A RentalWebsite agent physically inspects the property to confirm dimensions, photos, utilities and location. Once verified, your listing gets the Agency Verified badge — which significantly increases trust and click-through rate.'],
                ['n'=>'04','title'=>'Renters browse and save your property','body'=>'Your verified listing appears in search results and can be featured on the homepage. Renters save it, compare it and share it — all before contacting you.'],
                ['n'=>'05','title'=>'Receive serious viewing requests only','body'=>'Renters who want to view your property pay a small viewing fee to confirm their visit. That fee filters out casual enquiries — everyone who arrives has reviewed your listing and is genuinely interested.'],
                ['n'=>'06','title'=>'Agree terms and sign digitally','body'=>'Negotiate and agree terms through the platform. Sign the rental agreement digitally. Keep a record of everything in your landlord dashboard.'],
            ];
            foreach ($landlordSteps as $s): ?>
            <div class="hiw-step hiw-step--alt">
                <div class="hiw-step__num hiw-step__num--alt"><?= $s['n'] ?></div>
                <div class="hiw-step__body">
                    <h3 class="hiw-step__title"><?= $s['title'] ?></h3>
                    <p class="hiw-step__text"><?= $s['body'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ KEY FEATURES ═══════════════════════════════════════════ -->
<section class="section">
    <div class="home-section-inner">
        <div style="text-align:center;margin-bottom:48px">
            <span class="label">What makes us different</span>
            <h2 style="color:var(--dark-text)">Built around real information.</h2>
        </div>
        <div class="hiw-features">
            <?php
            $features = [
                ['title'=>'Exact Room Dimensions','body'=>'Every bedroom, kitchen and bathroom is measured by our agents — L×W×H. You know the exact size before visiting.'],
                ['title'=>'Road Access Data','body'=>'Walk time to main road, vehicle accessibility, road surface condition and public transport links — all confirmed on-site.'],
                ['title'=>'Utility Verification','body'=>'Electricity meter type (self, shared, prepaid), water source and frequency confirmed before the listing goes live.'],
                ['title'=>'Agency Verification','body'=>'Physical inspection by a RentalWebsite agent. Photos, dimensions and facilities confirmed before the listing is published.'],
                ['title'=>'Side-by-Side Comparison','body'=>'Compare up to 4 properties across every detail — rent, rooms, access, security, utilities — in one table.'],
                ['title'=>'Supervised Viewings','body'=>'Viewings are supervised and paid for in advance. Every visit is a serious one. No time-wasters for landlords or renters.'],
            ];
            foreach ($features as $f): ?>
            <div class="hiw-feature">
                <div class="hiw-feature__check">&#10003;</div>
                <div>
                    <div class="hiw-feature__title"><?= $f['title'] ?></div>
                    <div class="hiw-feature__body"><?= $f['body'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ FAQ ════════════════════════════════════════════════════ -->
<section class="section-alt">
    <div class="home-section-inner" style="max-width:760px">
        <div style="text-align:center;margin-bottom:40px">
            <span class="label">Common questions</span>
            <h2 style="color:var(--dark-text)">Frequently asked</h2>
        </div>
        <?php
        $faqs = [
            ['q'=>'Is it free to search for properties?','a'=>'Yes. Creating an account and browsing all property listings is completely free. You only pay a viewing fee when you decide to book a physical inspection.'],
            ['q'=>'What is the viewing fee for?','a'=>'The viewing fee covers the cost of our agent accompanying you to the property for a supervised visit. It filters out casual enquiries so every visit is a serious one — for both you and the landlord.'],
            ['q'=>'How do I know the property details are accurate?','a'=>'Every verified listing has been physically inspected by a RentalWebsite agent who confirms dimensions, photos, utilities, road access and landlord identity before the listing goes live. Look for the "Agency Verified" badge.'],
            ['q'=>'Can I list a property as a landlord?','a'=>'Yes. Register for a landlord account, add your property details and our team will contact you to arrange the physical verification inspection.'],
            ['q'=>'What happens if the property does not match the listing?','a'=>'If a property does not match the verified listing on arrival, report it through the platform. We investigate all reports and remove or suspend listings that do not meet our standards.'],
        ];
        foreach ($faqs as $i => $f): ?>
        <div style="border:1.5px solid var(--border);border-radius:12px;padding:20px 24px;margin-bottom:12px;background:#fff">
            <div style="font-size:15px;font-weight:700;color:var(--dark-text);margin-bottom:10px"><?= $f['q'] ?></div>
            <div style="font-size:14px;color:var(--muted);line-height:1.7"><?= $f['a'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ══ CTA ════════════════════════════════════════════════════ -->
<div class="cta-band">
    <div class="cta-band-inner">
        <h2 style="color:#DAF1DE">Ready to find your next home?</h2>
        <p style="color:#8EB69B">Search hundreds of verified properties with full room dimensions, road access data and utility information.</p>
        <div class="cta-band-btns">
            <a href="<?= Url::base('properties') ?>" class="btn btn-primary btn-lg" style="background:#DAF1DE;color:#051F20;border-color:#DAF1DE">Browse properties</a>
            <a href="<?= Url::base('auth/register') ?>" class="btn btn-lg" style="background:rgba(255,255,255,.08);color:#DAF1DE;border:1.5px solid rgba(218,241,222,.25)">Create free account</a>
        </div>
    </div>
</div>
