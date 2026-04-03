<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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
