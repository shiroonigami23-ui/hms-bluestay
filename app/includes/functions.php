<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (!isset($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
}

function validate_csrf_from_request(): void
{
    $token = (string) ($_POST['_csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    $valid = isset($_SESSION['_csrf_token']) && is_string($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
    if (!$valid) {
        http_response_code(419);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
}

function dashboard_title(string $role): string
{
    return match ($role) {
        'owner' => 'Owner Dashboard',
        'admin' => 'Admin Dashboard',
        'manager' => 'Manager Dashboard',
        'reception' => 'Front Desk Dashboard',
        'housekeeping' => 'Housekeeping Dashboard',
        'kitchen' => 'Kitchen Dashboard',
        'security' => 'Security Dashboard',
        'customer' => 'Customer Dashboard',
        default => 'Dashboard',
    };
}
