<?php
declare(strict_types=1);

session_start();

$config = require dirname(__DIR__) . '/config/config.php';
$GLOBALS['config'] = $config;

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $e): void {
    error_log(sprintf('[BlueStay HMS] %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
    if (!headers_sent()) {
        http_response_code(500);
    }
    $isApi = isset($_SERVER['REQUEST_URI']) && str_contains((string) $_SERVER['REQUEST_URI'], 'api.php');
    if ($isApi) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'Unexpected server error'], JSON_UNESCAPED_UNICODE);
    } else {
        echo '<!doctype html><html><head><meta charset="utf-8"><title>BlueStay HMS Error</title></head><body>';
        echo '<h2>Something went wrong.</h2><p>Please refresh or try again in a moment.</p></body></html>';
    }
    exit;
});

require dirname(__DIR__) . '/core/Database.php';
require dirname(__DIR__) . '/core/Auth.php';
require dirname(__DIR__) . '/core/ApiResponse.php';
require __DIR__ . '/functions.php';

$pdo = Database::conn($config['db']);
