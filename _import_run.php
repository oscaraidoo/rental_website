<?php
declare(strict_types=1);

// Statement-by-statement importer with full error logging.
// Writes progress to _import_log.txt so results survive the flaky shell.

$log = fopen('_import_log.txt', 'w');
function out(string $s, $log = null): void {
    if ($log) { fwrite($log, $s . PHP_EOL); fflush($log); }
}

$host = 'localhost'; $user = 'root'; $pass = '';
$scratch = 'audit_cleanroom_final';

try {
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    out('[ok] connected', $log);
    $pdo->exec("DROP DATABASE IF EXISTS `{$scratch}`");
    $pdo->exec("CREATE DATABASE `{$scratch}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$scratch}`");
    out("[ok] scratch {$scratch} created", $log);

    $sql = file_get_contents('database/import_to_production.sql');
    $stmts = array_filter(array_map('trim', explode(";\n", str_replace("\r\n", "\n", $sql))));
    out('[..] statements to run: ' . count($stmts), $log);

    $n = 0; $fail = 0;
    foreach ($stmts as $stmt) {
        if ($stmt === '' || str_starts_with($stmt, '--')) continue;
        $n++;
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            $fail++;
            out("[FAIL #{$n}] " . $e->getMessage(), $log);
            out('   stmt: ' . substr(preg_replace('/\s+/', ' ', $stmt), 0, 200), $log);
        }
    }
    out("[done] executed={$n} failed={$fail}", $log);

    // ── Post-import verification ──
    $tables = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='{$scratch}'")->fetchColumn();
    out("tables: {$tables}", $log);

    $fk = $pdo->query("SELECT COUNT(*) FROM information_schema.referential_constraints WHERE constraint_schema='{$scratch}'")->fetchColumn();
    out("foreign keys registered: {$fk}", $log);

    $cols = $pdo->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='{$scratch}' AND table_name='users' AND column_name IN ('google_id','auth_provider')")->fetchColumn();
    out("google oauth columns: {$cols}/2", $log);

    $zeroDates = $pdo->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='{$scratch}' AND column_type LIKE 'timestamp%' AND is_nullable='NO' AND column_default IS NULL")->fetchColumn();
    out("zero-default timestamps: {$zeroDates}", $log);

    out('[COMPLETE]', $log);
} catch (Throwable $e) {
    out('[FATAL] ' . $e->getMessage(), $log);
}
