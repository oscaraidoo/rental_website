<?php

declare(strict_types=1);

namespace App\Controllers\Renter;

use App\Controllers\BaseController;
use App\Models\PropertyModel;
use App\Helpers\{Security, Request, Database};
use App\Services\AuditService;

class ReportController extends BaseController
{
    public function create(string $propertyId): void
    {
        $this->renterOnly();

        $property = PropertyModel::findById((int) $propertyId);
        if (!$property) $this->notFound();

        $this->renterView('renter.reports.create', compact('property') + ['title' => 'Report Property']);
    }

    public function store(): void
    {
        $this->renterOnly();
        Security::requireCsrf();

        $propertyId  = (int) Request::post('property_id', 0);
        $property    = PropertyModel::findById($propertyId);

        if (!$property) {
            $this->error('Property not found.');
            $this->redirect('properties');
        }

        $reportType  = Security::sanitize(Request::post('report_type', 'other'));
        $description = Security::sanitize(Request::post('description', ''));

        $validTypes  = ['wrong_info','fake_property','wrong_dimensions','incorrect_rent','already_rented','misleading_photos','unsafe','fraud','other'];
        if (!in_array($reportType, $validTypes)) $reportType = 'other';

        if (strlen($description) < 20) {
            $this->error('Please provide a detailed description (at least 20 characters).');
            $this->redirect('renter/reports/create/' . $propertyId);
        }

        Database::insert('property_reports', [
            'property_id'  => $propertyId,
            'user_id'      => $this->currentUserId(),
            'report_type'  => $reportType,
            'description'  => $description,
            'status'       => 'open',
        ]);

        AuditService::log('report_property', 'reports', $propertyId, 'property');
        $this->success('Your report has been submitted. Our team will investigate.');
        $this->redirect('properties/' . $property['slug']);
    }
}
