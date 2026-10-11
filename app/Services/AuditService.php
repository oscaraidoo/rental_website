<?php
/**
 * Audit Log Service
 */

declare(strict_types=1);

namespace App\Services;

use App\Helpers\{Database, Security};

class AuditService
{
    public static function log(
        string $action,
        string $module,
        int|string|null $relatedId = null,   // DB ids often arrive as strings (PDO / session)
        string $relatedType = '',
        array  $oldValues   = [],
        array  $newValues   = [],
        string $description = ''
    ): void {
        // Normalize: session ids & PDO lastInsertId() come back as strings
        if ($relatedId !== null && $relatedId !== '') {
            $relatedId = (int) $relatedId;
        } else {
            $relatedId = null;
        }

        Database::insert('audit_logs', [
            'user_id'      => isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
            'action'       => $action,
            'module'       => $module,
            'related_id'   => $relatedId,
            'related_type' => $relatedType,
            'old_values'   => $oldValues ? json_encode($oldValues) : null,
            'new_values'   => $newValues ? json_encode($newValues) : null,
            'description'  => $description,
            'ip_address'   => Security::getIp(),
            'user_agent'   => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    }

    public static function all(int $limit = 50, int $offset = 0, array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['module'])) {
            $where[]  = 'al.module = ?';
            $params[] = $filters['module'];
        }
        if (!empty($filters['action'])) {
            $where[]  = 'al.action = ?';
            $params[] = $filters['action'];
        }
        if (!empty($filters['user_id'])) {
            $where[]  = 'al.user_id = ?';
            $params[] = (int) $filters['user_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = 'al.created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[]  = 'al.created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $params[] = $limit;
        $params[] = $offset;

        return Database::fetchAll(
            "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?",
            $params
        );
    }

    public static function count(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['module'])) { $where[] = 'module = ?'; $params[] = $filters['module']; }
        if (!empty($filters['action'])) { $where[] = 'action = ?'; $params[] = $filters['action']; }
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM audit_logs WHERE " . implode(' AND ', $where),
            $params
        );
    }
}
