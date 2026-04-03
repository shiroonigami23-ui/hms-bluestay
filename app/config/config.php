<?php
declare(strict_types=1);

function env(string $key, ?string $default = null): ?string
{
    static $env = null;
    if ($env === null) {
        $env = [];
        $envPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (is_file($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $env[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
            }
        }
    }
    return $env[$key] ?? $default;
}

date_default_timezone_set(env('TIMEZONE', 'Asia/Kolkata'));

return [
    'app' => [
        'name' => env('APP_NAME', 'BlueStay HMS'),
        'env' => env('APP_ENV', 'production'),
        'url' => env('APP_URL', ''),
    ],
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => (int) env('DB_PORT', '3306'),
        'name' => env('DB_NAME', 'hotel_management'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
    ],
];
