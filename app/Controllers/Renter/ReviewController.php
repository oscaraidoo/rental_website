<?php

declare(strict_types=1);

namespace App\Controllers\Renter;

use App\Controllers\BaseController;
use App\Models\ViewingModel;
use App\Helpers\{Security, Request, Database};
use App\Services\AuditService;

class ReviewController extends BaseController
{
    public function create(string $viewingId): void
    {
        $this->renterOnly();

        $viewing = ViewingModel::findById((int) $viewingId);
        if (!$viewing || $viewing['renter_id'] !== $this->currentUserId()) $this->notFound();

        if ($viewing['status'] !== 'completed') {
            $this->error('You can only review a property after completing a viewing.');
            $this->redirect('renter/viewings');
        }

        $existing = Database::fetchColumn(
            "SELECT id FROM reviews WHERE viewing_request_id = ?", [(int) $viewingId]
        );
        if ($existing) {
            $this->warning('You have already reviewed this viewing.');
            $this->redirect('renter/viewings/' . $viewingId);
        }

        $this->renterView('renter.reviews.create', compact('viewing') + ['title' => 'Leave a Review']);
    }

    public function store(): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $viewingId = (int) Request::post('viewing_id', 0);
        $viewing   = ViewingModel::findById($viewingId);

        if (!$viewing || $viewing['renter_id'] !== $this->currentUserId()) $this->notFound();

        if ($viewing['status'] !== 'completed') {
            $this->error('Reviews are only allowed for completed viewings.');
            $this->redirect('renter/viewings');
        }

        $existing = Database::fetchColumn(
            "SELECT id FROM reviews WHERE viewing_request_id = ?", [$viewingId]
        );
        if ($existing) {
            $this->error('You have already submitted a review for this viewing.');
            $this->redirect('renter/viewings/' . $viewingId);
        }

        $overall  = min(5, max(1, (int) Request::post('rating_overall', 3)));
        $comment  = Security::sanitize(Request::post('comment', ''));

        if (strlen($comment) < 10) {
            $this->error('Please write at least 10 characters in your review.');
            $this->redirect('renter/reviews/create/' . $viewingId);
        }

        Database::insert('reviews', [
            'property_id'        => $viewing['property_id'],
            'user_id'            => $this->currentUserId(),
            'viewing_request_id' => $viewingId,
            'rating_overall'     => $overall,
            'rating_accuracy'    => min(5, max(1, (int) Request::post('rating_accuracy', 3))),
            'rating_condition'   => min(5, max(1, (int) Request::post('rating_condition', 3))),
            'rating_location'    => min(5, max(1, (int) Request::post('rating_location', 3))),
            'rating_agent'       => min(5, max(1, (int) Request::post('rating_agent', 3))),
            'title'              => Security::sanitize(Request::post('review_title', '')),
            'comment'            => $comment,
            'status'             => 'pending',
        ]);

        AuditService::log('submit_review', 'reviews', (int) $viewing['property_id'], 'property');
        $this->success('Thank you! Your review has been submitted and is pending approval.');
        $this->redirect('renter/viewings/' . $viewingId);
    }
}
