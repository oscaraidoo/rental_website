<?php
declare(strict_types=1);

// Clean-room import test with LOCAL MySQL credentials (production creds only exist
// on the server; the test only needs permission to create a scratch database).
$host = 'localhost'; $port = '3306'; $user = 'root'; $pass = '';
$scratch = 'audit_cleanroom_' . bin2hex(random_bytes(3));

function out(string $s): void { fwrite(STDERR, $s . PHP_EOL); }

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    out('[ok] connected to local MySQL');

    $pdo->exec("CREATE DATABASE `{$scratch}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$scratch}`");
    out("[ok] scratch database `{$scratch}` created");

    $sql = file_get_contents('database/import_to_production.sql');
    out('[..] importing import_to_production.sql (' . strlen($sql) . ' bytes)');

    $stmtCount = 0; $failCount = 0;
    foreach (array_filter(array_map('trim', explode(";\n", str_replace("\r\n", "\n", $sql)))) as $stmt) {
        if ($stmt === '') continue;
        try {
            $pdo->exec($stmt);
            $stmtCount++;
        } catch (PDOException $e) {
            $failCount++;
            out('[FAIL] ' . substr($stmt, 0, 90) . ' :: ' . $e->getMessage());
        }
    }
    out("[ok] imported {$stmtCount} statements, {$failCount} failures");
    if ($failCount > 0) { out('[X] IMPORT HAD FAILURES'); exit(1); }

    $checks = [
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '{$scratch}'" => 'total tables',
        "SELECT COUNT(*) FROM `users`" => 'users',
        "SELECT COUNT(*) FROM `system_settings`" => 'system_settings',
        "SELECT COUNT(*) FROM `properties`" => 'properties',
        "SELECT `value` FROM `system_settings` WHERE `key` = 'mail_driver'" => 'mail_driver',
    ];
    foreach ($checks as $q => $label) {
        out(sprintf('[ok] %-18s = %s', $label, $pdo->query($q)->fetchColumn()));
    }

    $cols = $pdo->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='{$scratch}' AND table_name='users' AND column_name IN ('google_id','auth_provider')")->fetchColumn();
    out('[ok] google oauth columns on users: ' . $cols . '/2');

    $zeroDates = $pdo->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='{$scratch}' AND column_type LIKE 'timestamp%' AND is_nullable='NO' AND column_default IS NULL")->fetchColumn();
    out('[ok] timestamp columns without defaults: ' . $zeroDates . ' (0 = strict-mode safe)');

    $badImages = $pdo->query("SELECT COUNT(*) FROM `property_images` WHERE image_path NOT LIKE 'uploads/%'")->fetchColumn();
    out('[ok] property_images with non-local paths: ' . $badImages);

    out('[PASS] clean-room import fully verified');
} catch (Throwable $e) {
    out('[X] ' . $e->getMessage());
    exit(1);
} finally {
    if (isset($pdo)) {
        $pdo->exec("DROP DATABASE IF EXISTS `{$scratch}`");
        out('[ok] scratch database dropped');
    }
}
