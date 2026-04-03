<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$key = (string) ($_POST['key'] ?? '');
$expected = hash('sha256', ($config['db']['name'] ?? '') . '|' . ($config['db']['user'] ?? '') . '|' . ($config['db']['pass'] ?? ''));
if (!$key || !hash_equals($expected, $key)) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$lockFile = dirname(__DIR__) . '/app/.migration.lock';
if (is_file($lockFile)) {
    echo 'Already migrated';
    exit;
}

$schemaFile = dirname(__DIR__) . '/database/schema.sql';
$seedFile = dirname(__DIR__) . '/database/seed.sql';
if (!is_file($schemaFile) || !is_file($seedFile)) {
    http_response_code(500);
    echo 'Migration files missing';
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($config['db']['host'], $config['db']['user'], $config['db']['pass']);
$conn->set_charset('utf8mb4');

$schemaSql = file_get_contents($schemaFile) ?: '';
$seedSql = file_get_contents($seedFile) ?: '';

$conn->multi_query($schemaSql);
while ($conn->more_results() && $conn->next_result()) {
}
$conn->multi_query($seedSql);
while ($conn->more_results() && $conn->next_result()) {
}

file_put_contents($lockFile, date('c'));
echo 'Migration complete';
