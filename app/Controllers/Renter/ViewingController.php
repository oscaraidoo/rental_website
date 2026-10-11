<?php

declare(strict_types=1);

namespace App\Controllers\Renter;

use App\Controllers\BaseController;
use App\Models\{ViewingModel, PropertyModel, PaymentModel};
use App\Helpers\{Security, Request, Paginator, Database};
use App\Services\{NotificationService, AuditService};

class ViewingController extends BaseController
{
    public function index(): void
    {
        $this->renterOnly();

        $userId = $this->currentUserId();
        $total  = (int) Database::fetchColumn("SELECT COUNT(*) FROM viewing_requests WHERE renter_id = ?", [$userId]);
        $pager  = Paginator::fromRequest($total, 15);
        $viewings = ViewingModel::forRenter($userId, $pager->perPage, $pager->offset);

        $this->renterView('renter.viewings.index', compact('viewings', 'pager', 'total') + ['title' => 'My Viewings']);
    }

    public function show(string $id): void
    {
        $this->renterOnly();

        $viewing = ViewingModel::findById((int) $id);
        if (!$viewing || $viewing['renter_id'] !== $this->currentUserId()) $this->notFound();

        // Check if renter can leave a review
        $canReview = $viewing['status'] === 'completed' && !Database::fetchColumn(
            "SELECT id FROM reviews WHERE viewing_request_id = ?", [(int) $id]
        );

        $this->renterView('renter.viewings.show', compact('viewing', 'canReview') + [
            'title' => 'Viewing: ' . $viewing['reference'],
        ]);
    }

    public function requestForm(string $propertyId): void
    {
        $this->renterOnly();

        $property = PropertyModel::findById((int) $propertyId);
        if (!$property || $property['status'] !== 'verified') $this->notFound();

        $viewingFee = ViewingModel::getViewingFee((int) $propertyId);

        // Get available slots
        $slots = Database::fetchAll(
            "SELECT * FROM viewing_slots
             WHERE property_id = ? AND slot_date >= CURDATE() AND is_available = 1
               AND (booked_count < max_viewers)
             ORDER BY slot_date, slot_time
             LIMIT 30",
            [(int) $propertyId]
        );

        $cfg = require ROOT_PATH . '/config/app.php';
        $minDate = date('Y-m-d', strtotime('+' . ($cfg['viewing_advance_days'] ?? 1) . ' days'));
        $maxDate = date('Y-m-d', strtotime('+' . ($cfg['viewing_max_advance_days'] ?? 30) . ' days'));

        $this->renterView('renter.viewings.request', compact('property', 'viewingFee', 'slots', 'minDate', 'maxDate') + [
            'title' => 'Request Viewing: ' . $property['title'],
        ]);
    }

    public function submitRequest(): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $propertyId = (int) Request::post('property_id', 0);
        $property   = PropertyModel::findById($propertyId);

        if (!$property || $property['status'] !== 'verified') {
            $this->error('This property is not available for viewing.');
            $this->redirect('properties');
        }

        // Check if renter already has a pending/confirmed viewing for this property
        $existing = Database::fetchColumn(
            "SELECT id FROM viewing_requests
             WHERE property_id = ? AND renter_id = ?
               AND status IN ('pending_payment','paid','requested','confirmed')",
            [$propertyId, $this->currentUserId()]
        );
        if ($existing) {
            $this->error('You already have an active viewing request for this property.');
            $this->redirect('renter/viewings/' . $existing);
        }

        $prefDate = Security::sanitize(Request::post('preferred_date', ''));
        $prefTime = Security::sanitize(Request::post('preferred_time', ''));
        $altDate  = Security::sanitize(Request::post('alt_date', ''));
        $altTime  = Security::sanitize(Request::post('alt_time', ''));

        if (empty($prefDate) || empty($prefTime)) {
            $this->error('Please select a preferred date and time.');
            $this->redirect('renter/viewings/request/' . $propertyId);
        }

        $viewingFee = ViewingModel::getViewingFee($propertyId);

        $viewingId = ViewingModel::create([
            'property_id'    => $propertyId,
            'renter_id'      => $this->currentUserId(),
            'preferred_date' => $prefDate,
            'preferred_time' => $prefTime,
            'alt_date'       => $altDate ?: null,
            'alt_time'       => $altTime ?: null,
            'num_attendees'  => max(1, (int) Request::post('num_attendees', 1)),
            'special_request'=> Security::sanitize(Request::post('special_request', '')),
            'viewing_fee'    => $viewingFee,
            'status'         => 'pending_payment',
        ]);

        // Notify landlord
        $landlordUserId = Database::fetchColumn(
            "SELECT u.id FROM landlords l JOIN users u ON u.id = l.user_id WHERE l.id = ?",
            [$property['landlord_id']]
        );
        if ($landlordUserId) {
            NotificationService::viewingRequested(
                (int) $landlordUserId,
                $_SESSION['user_name'] ?? 'A renter',
                $property['title'],
                $viewingId
            );
        }

        AuditService::log('create_viewing_request', 'viewings', $viewingId, 'viewing');
        $this->success('Viewing request created. Please pay the viewing fee to confirm.');
        $this->redirect('renter/viewings/' . $viewingId);
    }

    public function cancel(string $id): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $viewing = ViewingModel::findById((int) $id);
        if (!$viewing || $viewing['renter_id'] !== $this->currentUserId()) $this->notFound();

        if (!in_array($viewing['status'], ['pending_payment', 'paid', 'requested', 'confirmed'])) {
            $this->error('This viewing cannot be cancelled in its current status.');
            $this->redirect('renter/viewings/' . $id);
        }

        $cfg = require ROOT_PATH . '/config/app.php';
        $cancelHrs = (int) ($cfg['viewing_cancellation_hrs'] ?? 24);
        if ($viewing['status'] === 'confirmed' && $viewing['confirmed_date']) {
            $hoursUntil = (strtotime($viewing['confirmed_date'] . ' ' . $viewing['confirmed_time']) - time()) / 3600;
            if ($hoursUntil < $cancelHrs) {
                $this->error("Viewings can only be cancelled at least {$cancelHrs} hours in advance.");
                $this->redirect('renter/viewings/' . $id);
            }
        }

        ViewingModel::update((int) $id, [
            'status'              => 'cancelled',
            'cancellation_reason' => Security::sanitize(Request::post('reason', 'Cancelled by renter')),
            'cancelled_by'        => $this->currentUserId(),
            'cancelled_at'        => date('Y-m-d H:i:s'),
        ]);

        // Notify the landlord that the viewing was cancelled
        $landlordUserId = Database::fetchColumn(
            "SELECT u.id FROM viewing_requests v
             JOIN properties p ON p.id = v.property_id
             JOIN landlords l ON l.id = p.landlord_id
             JOIN users u ON u.id = l.user_id
             WHERE v.id = ?",
            [(int) $id]
        );
        if ($landlordUserId) {
            NotificationService::viewingCancelled(
                (int) $landlordUserId,
                $viewing['property_title'] ?? 'your property',
                (int) $id,
                $viewing['cancellation_reason'] ?? '',
                true
            );
        }

        AuditService::log('cancel_viewing', 'viewings', (int) $id, 'viewing');
        $this->success('Viewing request cancelled.');
        $this->redirect('renter/viewings');
    }

    public function initiatePayment(string $id): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $viewing = ViewingModel::findById((int) $id);
        if (!$viewing || $viewing['renter_id'] !== $this->currentUserId()) $this->notFound();

        if ($viewing['status'] !== 'pending_payment') {
            $this->error('Payment is not required for this viewing.');
            $this->redirect('renter/viewings/' . $id);
        }

        // Create a pending payment record
        $paymentId = PaymentModel::create([
            'user_id'        => $this->currentUserId(),
            'amount'         => $viewing['viewing_fee'],
            'currency'       => 'GHS',
            'purpose'        => 'viewing_fee',
            'related_id'     => (int) $id,
            'related_type'   => 'viewing_request',
            'gateway'        => 'manual',
            'status'         => 'pending',
            'payer_name'     => $_SESSION['user_name'] ?? '',
            'payer_email'    => $_SESSION['user_email'] ?? '',
        ]);

        // In development: auto-confirm payment (real gateway integration here)
        $env = $_ENV['APP_ENV'] ?? 'development';
        if ($env === 'development') {
            PaymentModel::markSuccessful($paymentId, 'DEV-' . strtoupper(bin2hex(random_bytes(4))));

            ViewingModel::update((int) $id, [
                'status'     => 'paid',
                'fee_paid'   => 1,
                'payment_id' => $paymentId,
            ]);

            AuditService::log('pay_viewing_fee', 'payments', $paymentId, 'payment');
            $this->success('Payment confirmed (dev mode). Your viewing request is now confirmed.');
            $this->redirect('renter/viewings/' . $id);
        }

        // Production: redirect to payment gateway
        // $gatewayUrl = PaystackService::initialize($paymentId, $viewing['viewing_fee'], $_SESSION['user_email']);
        // $this->redirect($gatewayUrl);

        $this->success('Payment initiated. Complete payment to confirm your viewing.');
        $this->redirect('renter/viewings/' . $id);
    }

    public function paymentReturn(string $id): void
    {
        $this->renterOnly();

        $viewing = ViewingModel::findById((int) $id);
        if (!$viewing || $viewing['renter_id'] !== $this->currentUserId()) $this->notFound();

        $this->redirect('renter/viewings/' . $id);
    }
}
