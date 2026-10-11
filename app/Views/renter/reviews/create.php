<?php use App\Helpers\{Url, Security, Str}; ?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold border-0 py-3">
                <i class="fa-solid fa-star text-warning me-2"></i>Leave a Review
            </div>
            <div class="card-body">
                <div class="alert alert-info small mb-4">
                    <i class="fa-solid fa-info-circle me-2"></i>
                    You're reviewing: <strong><?= Security::e($viewing['property_title']) ?></strong>
                    (Viewing on <?= date('d M Y', strtotime($viewing['preferred_date'])) ?>)
                </div>

                <form method="POST" action="<?= Url::base('renter/reviews/store') ?>">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="viewing_id" value="<?= $viewing['id'] ?>">

                    <!-- Overall rating -->
                    <div class="mb-4">
                        <label class="form-label fw-medium">Overall Rating <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input d-none" type="radio" name="rating_overall" id="overall_<?= $i ?>" value="<?= $i ?>" required>
                                <label class="form-check-label fs-3 cursor-pointer star-label" for="overall_<?= $i ?>" style="cursor:pointer;color:#dee2e6">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </label>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Sub-ratings -->
                    <?php
                    $subRatings = [
                        'rating_accuracy'  => 'Accuracy of Listing',
                        'rating_condition' => 'Property Condition',
                        'rating_location'  => 'Location Accuracy',
                        'rating_agent'     => 'Agent / Experience',
                    ];
                    foreach ($subRatings as $name => $label): ?>
                    <div class="mb-3">
                        <label class="form-label fw-medium small"><?= $label ?></label>
                        <div class="d-flex gap-1 align-items-center">
                            <input type="range" class="form-range" name="<?= $name ?>" min="1" max="5" step="1" value="3"
                                   oninput="this.nextElementSibling.textContent=this.value+'/5'">
                            <span class="badge bg-primary ms-2 flex-shrink-0">3/5</span>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Review Title</label>
                        <input type="text" name="review_title" class="form-control"
                               placeholder="Summary of your experience..." maxlength="255">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">Your Review <span class="text-danger">*</span></label>
                        <textarea name="comment" class="form-control" rows="5" required minlength="10" maxlength="2000"
                                  placeholder="Share your experience about the property and the viewing. Was the listing accurate? What was the property condition like? Would you recommend it?"></textarea>
                        <div class="form-text">Minimum 10 characters. Your review will be moderated before publication.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-paper-plane me-2"></i>Submit Review
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
