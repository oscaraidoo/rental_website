<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PropertyModel;
use App\Helpers\{Security, Request, Paginator, Database};
use App\Services\{AuditService, NotificationService};

class VerificationController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();

        $filters = ['status' => Request::get('status', 'submitted')];
        $total      = PropertyModel::count($filters);
        $pager      = Paginator::fromRequest($total, 20);
        $properties = PropertyModel::all($pager->perPage, $pager->offset, $filters);

        // Also load pending verification records
        $pendingVerifications = Database::fetchAll(
            "SELECT pv.*, p.title, p.reference, p.id AS property_id
             FROM property_verifications pv
             JOIN properties p ON p.id = pv.property_id
             WHERE pv.status = 'pending'
             ORDER BY pv.created_at DESC
             LIMIT 20"
        );

        $this->adminView('admin.verifications.index', compact('properties', 'pendingVerifications', 'pager', 'total', 'filters') + [
            'title' => 'Property Verifications', 'breadcrumb' => ['Verifications'],
        ]);
    }

    public function show(string $id): void
    {
        $this->adminOnly();
        $property = PropertyModel::findById((int) $id, true);
        if (!$property) $this->notFound();

        $verification  = PropertyModel::getVerification((int) $id);
        $statusHistory = PropertyModel::getStatusHistory((int) $id);
        $amenityList   = Database::fetchAll("SELECT id, name, category FROM amenities WHERE active=1 ORDER BY category, sort_order");
        $ceiltypes     = Database::fetchAll("SELECT id, name FROM ceiling_types WHERE active=1");
        $floortypes    = Database::fetchAll("SELECT id, name FROM flooring_types WHERE active=1");

        $this->adminView('admin.verifications.show', compact(
            'property', 'verification', 'statusHistory', 'amenityList', 'ceiltypes', 'floortypes'
        ) + ['title' => 'Verify: ' . $property['reference'], 'breadcrumb' => ['Verifications', $property['reference']]]);
    }

    public function complete(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $property = PropertyModel::findById((int) $id);
        if (!$property) $this->notFound();

        $checks = [
            'dimensions_confirmed'   => !empty(Request::post('dimensions_confirmed')),
            'images_confirmed'       => !empty(Request::post('images_confirmed')),
            'facilities_confirmed'   => !empty(Request::post('facilities_confirmed')),
            'accessibility_confirmed'=> !empty(Request::post('accessibility_confirmed')),
            'location_confirmed'     => !empty(Request::post('location_confirmed')),
            'rent_confirmed'         => !empty(Request::post('rent_confirmed')),
            'landlord_id_confirmed'  => !empty(Request::post('landlord_id_confirmed')),
            'utilities_confirmed'    => !empty(Request::post('utilities_confirmed')),
            'availability_confirmed' => !empty(Request::post('availability_confirmed')),
        ];

        $passed = array_sum($checks);
        $total  = count($checks);
        $score  = (int) round(($passed / $total) * 100);
        $result = Security::sanitize(Request::post('result', 'passed'));
        $notes  = Security::sanitize(Request::post('notes', ''));
        $verRef = 'VER-' . strtoupper(bin2hex(random_bytes(4)));

        // Upsert verification record
        $existing = Database::fetchColumn(
            "SELECT id FROM property_verifications WHERE property_id = ? AND status = 'pending'",
            [(int) $id]
        );

        $verData = array_merge($checks, [
            'verified_by'      => $this->currentUserId(),
            'status'           => $result,
            'verification_ref' => $verRef,
            'overall_score'    => $score,
            'notes'            => $notes,
            'completed_at'     => date('Y-m-d H:i:s'),
        ]);

        if ($existing) {
            Database::update('property_verifications', $verData, 'property_id = ? AND status = ?', [(int) $id, 'pending']);
        } else {
            Database::insert('property_verifications', array_merge($verData, ['property_id' => (int) $id]));
        }

        if ($result === 'passed') {
            PropertyModel::updateStatus((int) $id, 'verified', $this->currentUserId());
            PropertyModel::update((int) $id, ['verified' => 1, 'verification_ref' => $verRef, 'verified_at' => date('Y-m-d H:i:s'), 'verified_by' => $this->currentUserId()]);

            $landlordUserId = Database::fetchColumn("SELECT user_id FROM landlords WHERE id = ?", [$property['landlord_id']]);
            if ($landlordUserId) {
                NotificationService::propertyVerified((int)$landlordUserId, $property['title'], (int) $id, $verRef);
            }
        } elseif ($result === 'failed') {
            PropertyModel::updateStatus((int) $id, 'rejected', $this->currentUserId(), $notes);

            $landlordUserId = Database::fetchColumn("SELECT user_id FROM landlords WHERE id = ?", [$property['landlord_id']]);
            if ($landlordUserId) {
                NotificationService::propertyRejected((int)$landlordUserId, $property['title'], $notes ?: 'Verification checks were not passed', (int) $id);
            }
        }

        AuditService::log('complete_verification', 'verifications', (int) $id, 'property', [], ['result' => $result, 'score' => $score]);
        $this->success("Verification completed. Result: {$result}. Score: {$score}%");
        $this->redirect('admin/verifications');
    }
}
