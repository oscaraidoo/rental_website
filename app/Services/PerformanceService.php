<?php
/**
 * Performance Service — lightweight request metrics collection.
 * Records one row per HTTP request into `perf_log`:
 * duration, DB query count/time, slow queries, memory, PHP errors.
 * Collects NO personal information (no IPs, no user ids, no user agents).
 */

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;

class PerformanceService
{
    /** @var array{count:int,timeMs:float,slow:array} */
    public static array $dbStats = ['count' => 0, 'timeMs' => 0.0, 'slow' => []];

    private static bool $enabled  = false;
    private static ?string $route = null;
    private static string $method = 'GET';
    private static ?array $error  = null;
    private static bool $logged   = false;
    private static ?bool $enabledCache = null;

    /** Begin collecting for this request */
    public static function begin(): void
    {
        self::$enabled = self::isEnabled();
        if (!self::$enabled) return;
        self::$route  = null;
        self::$error  = null;
        self::$logged = false;
        self::$dbStats = ['count' => 0, 'timeMs' => 0.0, 'slow' => []];
    }

    /** Set the resolved route path (called by the router after matching) */
    public static function setRoute(string $route): void
    {
        if (self::$route === null) {
            self::$route  = mb_substr($route, 0, 190);
            self::$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        }
    }

    /** Record a non-fatal PHP error / exception summary for this request */
    public static function recordError(string $type, string $message): void
    {
        if (!self::$enabled) return;
        // Keep only the first error per request
        if (self::$error !== null) return;
        self::$error = [
            'type'    => mb_substr($type, 0, 32),
            'message' => mb_substr($message, 0, 500),
        ];
    }

    /** Write the perf_log row (idempotent — runs once per request, at shutdown) */
    public static function finish(): void
    {
        if (!self::$enabled || self::$logged) return;
        self::$logged = true;

        try {
            $durationMs = (int) round((microtime(true) - (defined('START_TIME') ? START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000);

            Database::insert('perf_log', [
                'day'           => date('Y-m-d'),
                'route'         => self::$route ?? '/(unmatched)',
                'method'        => self::$method,
                'status_code'   => http_response_code() ?: 0,
                'duration_ms'   => $durationMs,
                'query_count'   => min(65535, self::$dbStats['count']),
                'query_time_ms' => (int) round(self::$dbStats['timeMs']),
                'slow_queries'  => min(65535, count(self::$dbStats['slow'])),
                'memory_kb'     => (int) round(memory_get_peak_usage(true) / 1024),
                'error_type'    => self::$error['type'] ?? null,
                'error_message' => self::$error['message'] ?? null,
            ]);

            // Housekeeping: keep ~45 days of data (cheap opportunistic prune)
            if (random_int(1, 200) === 1) {
                Database::query("DELETE FROM perf_log WHERE day < DATE_SUB(CURDATE(), INTERVAL 45 DAY)");
            }
        } catch (\Throwable) {
            // Monitoring must never break the request
        }
    }

    /** Record a finished query's stats (called from Database) */
    public static function trackQuery(float $ms, string $sql): void
    {
        if (!self::$enabled) return;
        self::$dbStats['count']++;
        self::$dbStats['timeMs'] += $ms;
        if ($ms > 100 && count(self::$dbStats['slow']) < 20) {
            self::$dbStats['slow'][] = ['ms' => (int) round($ms), 'sql' => mb_substr(preg_replace('/\s+/', ' ', $sql), 0, 300)];
        }
    }

    /** Enabled when APP_DEBUG=false (production), or forced on via PERF_MONITOR=true (local measurement) */
    public static function isEnabled(): bool
    {
        if (self::$enabledCache !== null) return self::$enabledCache;
        if (filter_var($_ENV['PERF_MONITOR'] ?? 'false', FILTER_VALIDATE_BOOLEAN)) {
            self::$enabledCache = true;
        } else {
            self::$enabledCache = !filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN);
        }
        return self::$enabledCache;
    }
}
