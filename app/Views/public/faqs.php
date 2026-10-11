<?php use App\Helpers\Security; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="fw-bold mb-2">Frequently Asked Questions</h1>
            <p class="text-muted mb-5">Everything you need to know about using RentalWebsite.</p>

            <?php
            $catLabels = ['general'=>'General','renter'=>'For Renters','landlord'=>'For Landlords','payment'=>'Payments','verification'=>'Verification','viewing'=>'Viewings'];
            foreach ($grouped as $cat => $faqs):
            ?>
            <h5 class="fw-bold mt-5 mb-3 text-primary">
                <?= Security::e($catLabels[$cat] ?? ucfirst($cat)) ?>
            </h5>
            <div class="accordion mb-3" id="faq_<?= $cat ?>">
                <?php foreach ($faqs as $i => $faq): ?>
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?> fw-medium" type="button"
                                data-bs-toggle="collapse" data-bs-target="#faq_<?= $cat ?>_<?= $faq['id'] ?>">
                            <?= Security::e($faq['question']) ?>
                        </button>
                    </h2>
                    <div id="faq_<?= $cat ?>_<?= $faq['id'] ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>">
                        <div class="accordion-body text-muted"><?= nl2br(Security::e($faq['answer'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>

            <div class="card border-0 bg-light p-4 mt-5 text-center">
                <p class="mb-2 fw-medium">Still have questions?</p>
                <a href="<?= \App\Helpers\Url::base('contact') ?>" class="btn btn-primary px-4">Contact Us</a>
            </div>
        </div>
    </div>
</div>
