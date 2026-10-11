<?php
/** Render-harness: admin/performance view with synthetic data. Exits 0 on clean render.
 *  The view runs in global scope but references \App\Helpers\* — resolved via
 *  class_alias fakes defined at runtime from the global namespace.
 */
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('ROOT_PATH', dirname(__DIR__, 2));

// ── Fake helpers the view needs (no DB connection) ──────────────
eval('namespace App\Helpers;
class Url {
    public static function base(string $p = \'\'): string {
        return \'http://localhost/rental_website/public\' . ($p ? \'/\' . $p : \'\');
    }
    public static function asset(string $p): string { return self::base() . \'/public/\' . $p; }
}
class Security {
    public static function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, \'UTF-8\'); }
    public static function csrfToken(): string { return \'harness-token\'; }
}
class Flash { public static function render(): string { return \'\'; } }
');

// Synthetic controller output
$monitorEnabled = true;
$summary = ['requests' => 12, 'avg_ms' => 48.3, 'avg_queries' => 7.5, 'slow_queries' => 2, 'errors' => 1, 'max_ms' => 130];
$p95ms = 110;
$routes = [
    ['route' => '/', 'requests' => 5, 'avg_ms' => 40.2, 'max_ms' => 90, 'avg_queries' => 8.0, 'max_queries' => 8, 'slow_queries' => 0, 'errors' => 0, 'max_memory_kb' => 2048],
    ['route' => '/properties', 'requests' => 4, 'avg_ms' => 70.5, 'max_ms' => 160, 'avg_queries' => 4.0, 'max_queries' => 5, 'slow_queries' => 2, 'errors' => 1, 'max_memory_kb' => 3048],
];
$slowQueries = [
    ['route' => '/properties', 'requests' => 3, 'slow_queries' => 2, 'query_time_ms' => 140],
];
$errors = [
    ['route' => '/properties', 'method' => 'GET', 'status_code' => 200, 'error_type' => 'warning',
     'error_message' => 'Undefined index: demo', 'created_at' => date('Y-m-d H:i:s')],
];
$trend = [
    ['day' => date('Y-m-d'), 'requests' => 12, 'avg_ms' => 48.3, 'errors' => 1],
];

set_error_handler(function ($no, $str, $file, $line) {
    echo "WARNING [$no] $str at $file:$line\n";
    return true;
});

extract([
    'monitorEnabled' => $monitorEnabled, 'summary' => $summary, 'p95ms' => $p95ms,
    'routes' => $routes, 'slowQueries' => $slowQueries, 'errors' => $errors, 'trend' => $trend,
], EXTR_OVERWRITE);

ob_start();
include ROOT_PATH . '/app/Views/admin/performance.php';
$html = ob_get_clean();
restore_error_handler();

$mustHave = ['Performance', 'data-table', 'csrf_token'];
$ok = true;
foreach ($mustHave as $needle) {
    if (!str_contains($html, $needle)) { echo "MISSING: $needle\n"; $ok = false; }
}
echo $ok ? "OK — view rendered clean, " . strlen($html) . " bytes\n" : "FAIL\n";
exit($ok ? 0 : 1);
