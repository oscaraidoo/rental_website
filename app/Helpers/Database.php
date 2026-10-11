<?php
/**
 * Database Helper — Singleton PDO wrapper
 */

declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $cfg = require ROOT_PATH . '/config/database.php';
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $cfg['host'], $cfg['port'], $cfg['database'], $cfg['charset']
                );
                self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
            } catch (PDOException $e) {
                // Never expose credentials in error output
                throw new RuntimeException('Database connection failed. Check configuration.');
            }
        }
        return self::$instance;
    }

    /** Execute a prepared statement, return the statement object */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $t0 = microtime(true);
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        if (class_exists(\App\Services\PerformanceService::class)) {
            \App\Services\PerformanceService::trackQuery((microtime(true) - $t0) * 1000, $sql);
        }
        return $stmt;
    }

    /** Fetch all rows */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /** Fetch single row */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** Fetch single column value */
    public static function fetchColumn(string $sql, array $params = []): mixed
    {
        return self::query($sql, $params)->fetchColumn();
    }

    /** Insert row, return last insert ID */
    public static function insert(string $table, array $data): int|string
    {
        $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        self::query("INSERT INTO `$table` ($cols) VALUES ($placeholders)", array_values($data));
        return self::getInstance()->lastInsertId();
    }

    /** Update row(s), return affected rows */
    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
        $stmt = self::query(
            "UPDATE `$table` SET $set WHERE $where",
            [...array_values($data), ...$whereParams]
        );
        return $stmt->rowCount();
    }

    /** Begin transaction */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /** Commit transaction */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /** Rollback transaction */
    public static function rollBack(): void
    {
        self::getInstance()->rollBack();
    }

    /** Get last insert ID */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }
}
