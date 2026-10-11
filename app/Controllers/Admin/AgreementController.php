<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Helpers\{Database, Paginator, Request};

class AgreementController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();

        $total = (int) Database::fetchColumn("SELECT COUNT(*) FROM landlord_agreements");
        $pager = Paginator::fromRequest($total, 25);

        $agreements = Database::fetchAll(
            "SELECT la.*, CONCAT(u.first_name,' ',u.last_name) AS landlord_name, u.email
             FROM landlord_agreements la
             JOIN landlords l ON l.id = la.landlord_id
             JOIN users u ON u.id = l.user_id
             ORDER BY la.created_at DESC
             LIMIT ? OFFSET ?",
            [$pager->perPage, $pager->offset]
        );

        $this->adminView('admin.agreements.index', compact('agreements', 'pager', 'total') + [
            'title' => 'Agreements', 'breadcrumb' => ['Agreements'],
        ]);
    }

    public function show(string $id): void
    {
        $this->adminOnly();

        $agreement = Database::fetchOne(
            "SELECT la.*, CONCAT(u.first_name,' ',u.last_name) AS landlord_name, u.email,
                    p.title AS property_title
             FROM landlord_agreements la
             JOIN landlords l ON l.id = la.landlord_id
             JOIN users u ON u.id = l.user_id
             LEFT JOIN properties p ON p.id = la.property_id
             WHERE la.id = ?",
            [(int) $id]
        );
        if (!$agreement) $this->notFound();

        $signatures = Database::fetchAll(
            "SELECT ags.*, CONCAT(u.first_name,' ',u.last_name) AS signer_name
             FROM agreement_signatures ags JOIN users u ON u.id = ags.user_id
             WHERE ags.agreement_id = ?",
            [(int) $id]
        );

        $this->adminView('admin.agreements.show', compact('agreement', 'signatures') + [
            'title' => 'Agreement: ' . $agreement['agreement_ref'],
            'breadcrumb' => ['Agreements', $agreement['agreement_ref']],
        ]);
    }
}
