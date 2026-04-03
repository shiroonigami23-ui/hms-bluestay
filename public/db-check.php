<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';

$token = (string) ($_GET['token'] ?? '');
$expected = hash('sha256', ($config['db']['name'] ?? '') . '|' . ($config['db']['user'] ?? ''));
if (!$token || !hash_equals($expected, $token)) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
echo "DB: " . $config['db']['name'] . PHP_EOL;
$tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY table_name")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables: " . count($tables) . PHP_EOL;
foreach ($tables as $t) {
    echo "- {$t}" . PHP_EOL;
}
