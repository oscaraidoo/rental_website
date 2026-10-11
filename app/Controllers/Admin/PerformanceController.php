<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Helpers\Database;

/**
 * Admin performance monitoring page.
 *
 * Aggregates the perf_log table collected by PerformanceService:
 * avg/p95 duration, query counts, slow queries and errors per route
 * over the last 7 days. Contains no personal information.
 */
class PerformanceController extends BaseController
{
    public function index(): void
    {
        $this->adminOnly();

        // If monitoring is currently off, mirror the state back to the view
        $monitorEnabled = \App\Services\PerformanceService::isEnabled();

        // ── Summary (last 7 days) ────────────────────────────
        $summary = Database::fetchOne(
            "SELECT COUNT(*) AS requests,
                    COALESCE(ROUND(AVG(duration_ms),1),0) AS avg_ms,
                    COALESCE(MAX(duration_ms),0) AS max_ms,
                    COALESCE(ROUND(AVG(query_count),1),0) AS avg_queries,
                    COALESCE(SUM(slow_queries),0) AS slow_queries,
                    COALESCE(SUM(error_type IS NOT NULL),0) AS errors
             FROM perf_log
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        ) ?? [];

        // ── Per-route breakdown (last 7 days) ────────────────
        $routes = Database::fetchAll(
            "SELECT route,
                    COUNT(*) AS requests,
                    ROUND(AVG(duration_ms),1) AS avg_ms,
                    MAX(duration_ms) AS max_ms,
                    ROUND(AVG(query_count),1) AS avg_queries,
                    MAX(query_count) AS max_queries,
                    SUM(slow_queries) AS slow_queries,
                    SUM(error_type IS NOT NULL) AS errors,
                    MAX(memory_kb) AS max_memory_kb
             FROM perf_log
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY route
             ORDER BY AVG(duration_ms) DESC
             LIMIT 25"
        );

        // p95 computed in PHP (MySQL portable, no window functions needed)
        $durations = array_map('intval', array_column(
            Database::fetchAll(
                "SELECT duration_ms FROM perf_log
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 ORDER BY duration_ms"
            ),
            'duration_ms'
        ));
        $p95ms = empty($durations) ? 0 : (int) $durations[min(count($durations) - 1, (int) floor(count($durations) * 0.95))];

        // Slow queries are tracked in-memory per request (max 20 samples kept);
        // perf_log aggregates the count per route.
        $slowQueries = Database::fetchAll(
            "SELECT route, MAX(query_time_ms) AS query_time_ms, SUM(slow_queries) AS slow_queries, COUNT(*) AS requests
             FROM perf_log
             WHERE slow_queries > 0
               AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY route
             ORDER BY slow_queries DESC
             LIMIT 20"
        );

        // ── Errors (last 7 days) ─────────────────────────────
        $errors = Database::fetchAll(
            "SELECT route, method, status_code, error_type, error_message, created_at
             FROM perf_log
             WHERE error_type IS NOT NULL
               AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             ORDER BY created_at DESC
             LIMIT 20"
        );

        // ── Daily trend (last 14 days) ───────────────────────
        $trend = Database::fetchAll(
            "SELECT DATE(created_at) AS day,
                    COUNT(*) AS requests,
                    ROUND(AVG(duration_ms),1) AS avg_ms,
                    SUM(error_type IS NOT NULL) AS errors
             FROM perf_log
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
             GROUP BY DATE(created_at)
             ORDER BY day DESC"
        );

        $this->adminView('admin.performance', [
            'monitorEnabled' => $monitorEnabled,
            'summary'        => $summary,
            'routes'         => $routes,
            'p95ms'          => $p95ms,
            'slowQueries'    => $slowQueries,
            'errors'         => $errors,
            'trend'          => $trend,
        ] + ['title' => 'Performance', 'breadcrumb' => ['Performance']]);
    }

    public function clear(): void
    {
        $this->adminOnly();
        $this->requireCsrf();

        Database::query("DELETE FROM perf_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");

        $this->success('Old performance records (older than 7 days) cleared.');
        $this->redirect('admin/performance');
    }
}
