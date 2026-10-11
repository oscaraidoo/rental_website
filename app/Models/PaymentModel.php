<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\{Database, Str};
use App\Services\NotificationService;

class PaymentModel
{
    public static function findById(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT py.*, CONCAT(u.first_name,' ',u.last_name) AS user_name, u.email
             FROM payments py JOIN users u ON u.id = py.user_id WHERE py.id = ?",
            [$id]
        );
    }

    public static function findByRef(string $ref): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM payments WHERE transaction_ref = ?", [$ref]
        );
    }

    public static function create(array $data): int
    {
        if (empty($data['transaction_ref'])) {
            $data['transaction_ref'] = self::generateRef();
        }
        return (int) Database::insert('payments', $data);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('payments', $data, 'id = ?', [$id]);
    }

    public static function markSuccessful(int $id, string $gatewayRef = '', array $gatewayResponse = []): void
    {
        Database::update('payments', [
            'status'           => 'successful',
            'gateway_ref'      => $gatewayRef,
            'gateway_response' => $gatewayResponse ? json_encode($gatewayResponse) : null,
            'paid_at'          => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        // Notify the payer (event-driven, so every gateway path gets it for free)
        $payment = self::findById($id);
        if ($payment && $payment['status'] === 'successful') {
            NotificationService::paymentSuccessful(
                (int) $payment['user_id'],
                ucfirst(str_replace('_', ' ', (string) $payment['purpose'])),
                (float) $payment['amount'],
                $id,
                (string) ($payment['currency'] ?: 'GHS')
            );
        }
    }

    public static function markFailed(int $id, string $reason = ''): void
    {
        Database::update('payments', [
            'status'  => 'failed',
            'notes'   => $reason,
        ], 'id = ?', [$id]);

        $payment = self::findById($id);
        if ($payment) {
            NotificationService::paymentFailed(
                (int) $payment['user_id'],
                ucfirst(str_replace('_', ' ', (string) $payment['purpose'])),
                (float) $payment['amount'],
                $id,
                (string) ($payment['currency'] ?: 'GHS')
            );
        }
    }

    public static function all(int $limit = 25, int $offset = 0, array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['status']))  { $where[] = 'py.status = ?';  $params[] = $filters['status']; }
        if (!empty($filters['purpose'])) { $where[] = 'py.purpose = ?'; $params[] = $filters['purpose']; }
        if (!empty($filters['date_from'])) { $where[] = 'py.created_at >= ?'; $params[] = $filters['date_from'] . ' 00:00:00'; }
        if (!empty($filters['date_to']))   { $where[] = 'py.created_at <= ?'; $params[] = $filters['date_to']   . ' 23:59:59'; }

        $params[] = $limit;
        $params[] = $offset;

        return Database::fetchAll(
            "SELECT py.id, py.transaction_ref, py.amount, py.currency, py.purpose,
                    py.status, py.gateway, py.paid_at, py.created_at,
                    CONCAT(u.first_name,' ',u.last_name) AS user_name, u.email
             FROM payments py JOIN users u ON u.id = py.user_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY py.created_at DESC LIMIT ? OFFSET ?",
            $params
        );
    }

    public static function count(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) { $where[] = 'status = ?'; $params[] = $filters['status']; }
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM payments WHERE " . implode(' AND ', $where), $params
        );
    }

    public static function totalRevenue(string $dateFrom = '', string $dateTo = ''): float
    {
        $where  = ["status = 'successful'"];
        $params = [];
        if ($dateFrom) { $where[] = 'paid_at >= ?'; $params[] = $dateFrom . ' 00:00:00'; }
        if ($dateTo)   { $where[] = 'paid_at <= ?'; $params[] = $dateTo   . ' 23:59:59'; }
        return (float) Database::fetchColumn(
            "SELECT COALESCE(SUM(amount),0) FROM payments WHERE " . implode(' AND ', $where), $params
        );
    }

    public static function revenueByPurpose(): array
    {
        return Database::fetchAll(
            "SELECT purpose, SUM(amount) AS total, COUNT(*) AS count
             FROM payments WHERE status = 'successful'
             GROUP BY purpose"
        );
    }

    public static function forUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }

    private static function generateRef(): string
    {
        do {
            $ref = 'PAY-' . strtoupper(bin2hex(random_bytes(6)));
            $exists = Database::fetchColumn("SELECT id FROM payments WHERE transaction_ref = ?", [$ref]);
        } while ($exists);
        return $ref;
    }
}
