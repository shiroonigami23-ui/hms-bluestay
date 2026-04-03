<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$config = require dirname(__DIR__) . '/config/config.php';
$GLOBALS['config'] = $config;

if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https://commons.wikimedia.org https://upload.wikimedia.org; style-src 'self' 'unsafe-inline'; script-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
}

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

/**
 * Auto-creates schema/seed when DB exists but has no tables.
 * This is useful on fresh hosting setups like InfinityFree.
 */
function bootstrap_auto_migrate(PDO $pdo, array $config): void
{
    try {
        $dbName = (string) ($config['db']['name'] ?? '');
        if ($dbName === '') {
            return;
        }

        $check = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :db AND table_name = 'users'"
        );
        $check->execute(['db' => $dbName]);
        $exists = (int) $check->fetchColumn() > 0;
        if ($exists) {
            return;
        }

        $schemaFile = dirname(__DIR__, 2) . '/database/schema.sql';
        $seedFile = dirname(__DIR__, 2) . '/database/seed.sql';
        if (!is_file($schemaFile) || !is_file($seedFile)) {
            return;
        }

        $runSqlFile = static function (PDO $pdoConn, string $filePath): void {
            $raw = file_get_contents($filePath);
            if (!is_string($raw) || trim($raw) === '') {
                return;
            }

            $clean = preg_replace('/\\/\\*.*?\\*\\//s', '', $raw) ?? $raw;
            $lines = explode("\n", $clean);
            $buffer = '';
            foreach ($lines as $line) {
                $trim = trim($line);
                if ($trim === '' || str_starts_with($trim, '--')) {
                    continue;
                }
                $buffer .= $line . "\n";
            }
            $statements = array_filter(array_map('trim', explode(';', $buffer)));
            foreach ($statements as $stmt) {
                if ($stmt === '') {
                    continue;
                }
                $upper = strtoupper($stmt);
                if (str_starts_with($upper, 'CREATE DATABASE') || str_starts_with($upper, 'USE ')) {
                    continue;
                }
                try {
                    $pdoConn->exec($stmt);
                } catch (Throwable $inner) {
                    error_log('[BlueStay HMS] migration statement skipped: ' . $inner->getMessage());
                }
            }
        };

        $runSqlFile($pdo, $schemaFile);
        $runSqlFile($pdo, $seedFile);
    } catch (Throwable $e) {
        error_log('[BlueStay HMS] auto migration failed: ' . $e->getMessage());
    }
}

bootstrap_auto_migrate($pdo, $config);
