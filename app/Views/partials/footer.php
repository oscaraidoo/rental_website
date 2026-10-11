<?php use App\Helpers\{Url, Security}; ?>
<footer class="site-footer">
    <div class="container">
        <div class="row g-5">

            <!-- Brand col -->
            <div class="col-12 col-lg-4">
                <div class="footer-brand">RentalWebsite</div>
                <p class="footer-tagline">See it. Understand it. Then decide.</p>
                <p style="font-size:14px;color:rgba(255,255,255,.4);line-height:1.7;max-width:320px">
                    Ghana's property information and viewing management platform.
                    Know the accommodation before you go there.
                </p>
            </div>

            <!-- Platform -->
            <div class="col-6 col-md-3 col-lg-2">
                <div class="footer-heading">Platform</div>
                <ul class="footer-links">
                    <li><a href="<?= Url::base('properties') ?>">Search Properties</a></li>
                    <li><a href="<?= Url::base('how-it-works') ?>">How It Works</a></li>
                    <li><a href="<?= Url::base('for-renters') ?>">For Renters</a></li>
                    <li><a href="<?= Url::base('blog') ?>">Blog</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div class="col-6 col-md-3 col-lg-2">
                <div class="footer-heading">Support</div>
                <ul class="footer-links">
                    <li><a href="<?= Url::base('faqs') ?>">FAQs</a></li>
                    <li><a href="<?= Url::base('contact') ?>">Contact Us</a></li>
                    <li><a href="<?= Url::base('page/help') ?>">Help Centre</a></li>
                    <li><a href="<?= Url::base('page/viewing-policy') ?>">Viewing Policy</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div class="col-6 col-md-3 col-lg-2">
                <div class="footer-heading">Legal</div>
                <ul class="footer-links">
                    <li><a href="<?= Url::base('page/terms') ?>">Terms &amp; Conditions</a></li>
                    <li><a href="<?= Url::base('page/privacy') ?>">Privacy Policy</a></li>
                    <li><a href="<?= Url::base('page/agency-agreement') ?>">Agency Agreement</a></li>
                    <li><a href="<?= Url::base('page/fees') ?>">Fees &amp; Pricing</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-6 col-md-3 col-lg-2">
                <div class="footer-heading">Contact</div>
                <ul class="footer-links">
                    <li><a href="mailto:info@habellesfoundation.org">info@habellesfoundation.org</a></li>
                    <li><a href="<?= Url::base('contact') ?>">Contact form</a></li>
                    <li style="color:rgba(255,255,255,.35);font-size:14px">Ghana</li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> RentalWebsite. All rights reserved.</span>
            <span>Built in Ghana</span>
        </div>
    </div>
</footer>
