<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';

$token = (string) ($_GET['token'] ?? '');
$expected = hash('sha256', ($config['db']['name'] ?? '') . '|' . ($config['db']['user'] ?? '') . '|force');
if (!$token || !hash_equals($expected, $token)) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
echo "Starting force import...\n";

$schemaFile = dirname(__DIR__) . '/database/schema.sql';
$seedFile = dirname(__DIR__) . '/database/seed.sql';
if (!is_file($schemaFile) || !is_file($seedFile)) {
    echo "Missing SQL files.\n";
    exit;
}

$runFile = static function (PDO $pdo, string $file, string $label): void {
    echo "Running {$label}...\n";
    $raw = file_get_contents($file) ?: '';
    $raw = preg_replace('/\/\*.*?\*\//s', '', $raw) ?? $raw;
    $lines = explode("\n", $raw);
    $buffer = '';
    foreach ($lines as $line) {
        $trim = trim($line);
        if ($trim === '' || str_starts_with($trim, '--')) {
            continue;
        }
        $buffer .= $line . "\n";
    }
    $stmts = array_filter(array_map('trim', explode(';', $buffer)));
    $ok = 0;
    $skip = 0;
    $err = 0;
    foreach ($stmts as $stmt) {
        $upper = strtoupper($stmt);
        if (str_starts_with($upper, 'CREATE DATABASE') || str_starts_with($upper, 'USE ')) {
            $skip++;
            continue;
        }
        try {
            $pdo->exec($stmt);
            $ok++;
        } catch (Throwable $e) {
            $err++;
            echo "ERR: " . $e->getMessage() . "\n";
        }
    }
    echo "{$label} done. ok={$ok}, skip={$skip}, err={$err}\n";
};

try {
    $runFile($pdo, $schemaFile, 'schema');
    $runFile($pdo, $seedFile, 'seed');
    $count = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
    echo "Tables now: {$count}\n";
    echo "Force import complete.\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
