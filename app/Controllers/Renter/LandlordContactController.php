<?php

declare(strict_types=1);

namespace App\Controllers\Renter;

use App\Controllers\BaseController;
use App\Models\{PropertyModel, PaymentModel, SettingsModel};
use App\Helpers\{Security, Request, Database};
use App\Services\{AuditService, NotificationService};

class LandlordContactController extends BaseController
{
    private const FALLBACK_FEE = 50.00;

    /**
     * Renter pays the unlock fee to view the landlord's contact details
     * for a specific property. In development the payment is auto-confirmed;
     * in production this would redirect to a payment gateway.
     */
    public function unlock(string $propertyId): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $propertyId = (int) $propertyId;
        $userId     = $this->currentUserId();
        $property   = PropertyModel::findById($propertyId);

        if (!$property || $property['status'] !== 'verified') {
            $this->error('This property is not available.');
            $this->redirect('properties');
        }

        // Already unlocked — go straight to the contact page.
        if ($this->isUnlocked($propertyId, $userId)) {
            $this->redirect('renter/landlord-contact/view/' . $propertyId);
        }

        $fee = $this->unlockFee();

        // Create a pending payment record (mirrors the viewing-fee flow).
        $paymentId = PaymentModel::create([
            'user_id'      => $userId,
            'amount'       => $fee,
            'currency'     => 'GHS',
            'purpose'      => 'landlord_contact_unlock',
            'related_id'   => $propertyId,
            'related_type' => 'property',
            'gateway'      => 'manual',
            'status'       => 'pending',
            'payer_name'   => $_SESSION['user_name']  ?? '',
            'payer_email'  => $_SESSION['user_email'] ?? '',
        ]);

        $env = $_ENV['APP_ENV'] ?? 'development';

        if ($env === 'development') {
            PaymentModel::markSuccessful($paymentId, 'DEV-' . strtoupper(bin2hex(random_bytes(4))));

            $this->recordUnlock($propertyId, $userId, $fee, $paymentId);

            AuditService::log('unlock_landlord_contact', 'properties', $propertyId, 'property');
            $this->success('Payment confirmed. You can now view the landlord\'s contact details.');
            $this->redirect('renter/landlord-contact/view/' . $propertyId);
        }

        // Production: redirect to the configured payment gateway here.
        $this->success('Payment initiated. Complete payment to view the landlord\'s contact details.');
        $this->redirect('renter/landlord-contact/view/' . $propertyId);
    }

    /**
     * Display the landlord's contact details for a property — but only if the
     * renter has paid to unlock them. Otherwise send them back to the listing
     * with a prompt to pay.
     */
    public function show(string $propertyId): void
    {
        $this->renterOnly();

        $propertyId = (int) $propertyId;
        $userId     = $this->currentUserId();
        $property   = PropertyModel::findById($propertyId, true);

        if (!$property) {
            $this->notFound();
        }

        if (!$this->isUnlocked($propertyId, $userId)) {
            $this->warning('Please pay the unlock fee to view the landlord\'s contact details.');
            $this->redirect('properties/' . ($property['slug'] ?? ''));
        }

        $contact = $this->contactFor($property);
        $unlock  = $this->unlockRecord($propertyId, $userId);

        $this->renterView('renter.landlord_contact.view', compact('property', 'contact', 'unlock') + [
            'title' => 'Landlord Contact — ' . $property['title'],
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────

    public static function unlockFee(): float
    {
        return (float) SettingsModel::get('landlord_contact_fee', self::FALLBACK_FEE);
    }

    public static function isUnlocked(int $propertyId, int $userId): bool
    {
        return (bool) Database::fetchColumn(
            "SELECT id FROM landlord_contact_unlocks
             WHERE property_id = ? AND user_id = ? AND status = 'paid'
             LIMIT 1",
            [$propertyId, $userId]
        );
    }

    private function unlockRecord(int $propertyId, int $userId): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM landlord_contact_unlocks
             WHERE property_id = ? AND user_id = ?
             ORDER BY id DESC LIMIT 1",
            [$propertyId, $userId]
        );
    }

    private function recordUnlock(int $propertyId, int $userId, float $fee, int $paymentId): void
    {
        Database::query(
            "INSERT INTO landlord_contact_unlocks
                (property_id, user_id, amount, payment_ref, status, paid_at)
             VALUES (?, ?, ?, ?, 'paid', NOW())
             ON DUPLICATE KEY UPDATE
                amount = VALUES(amount),
                payment_ref = VALUES(payment_ref),
                status = 'paid',
                paid_at = NOW()",
            [$propertyId, $userId, $fee, 'PAY-' . $paymentId]
        );
    }

    private function contactFor(array $property): array
    {
        return [
            'name'    => trim(($property['landlord_first'] ?? '') . ' ' . ($property['landlord_last'] ?? '')),
            'phone'   => $property['landlord_contact_phone']   ?? null,
            'phone2'  => $property['landlord_contact_phone2']  ?? null,
            'email'   => $property['landlord_contact_email']   ?? null,
            'address' => $property['landlord_contact_address'] ?? null,
            'notes'   => $property['landlord_contact_notes']   ?? null,
        ];
    }
}
