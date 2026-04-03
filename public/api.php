<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';

if (!Auth::check() && ($_GET['action'] ?? '') !== 'auth.ping') {
    ApiResponse::json(['ok' => false, 'message' => 'Unauthorized'], 401);
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$user = Auth::user();

function getJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

switch ($action) {
    case 'auth.ping':
        ApiResponse::json(['ok' => true, 'app' => 'BlueStay HMS']);
        break;

    case 'dashboard.stats':
        $stmt = $pdo->query("
            SELECT
              (SELECT COUNT(*) FROM users) AS users,
              (SELECT COUNT(*) FROM bookings) AS bookings,
              (SELECT COUNT(*) FROM service_requests WHERE status IN ('open','in_progress')) AS open_requests,
              (SELECT IFNULL(SUM(total_amount), 0) FROM invoices) AS total_revenue
        ");
        ApiResponse::json(['ok' => true, 'data' => $stmt->fetch()]);
        break;

    case 'rooms.list':
        $stmt = $pdo->query("SELECT id, room_number, floor_no, room_type, status, base_rate FROM rooms ORDER BY floor_no, room_number");
        ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'rooms.updateStatus':
        Auth::requireRole(['owner', 'admin', 'manager', 'reception', 'housekeeping']);
        $body = $method === 'POST' ? $_POST : getJsonBody();
        $stmt = $pdo->prepare("UPDATE rooms SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $body['status'] ?? 'available', 'id' => (int) ($body['id'] ?? 0)]);
        ApiResponse::json(['ok' => true, 'message' => 'Room status updated']);
        break;

    case 'bookings.list':
        $stmt = $pdo->query("
            SELECT b.id, b.booking_code, b.status, b.check_in, b.check_out, r.room_number, u.full_name AS guest_name
            FROM bookings b
            JOIN rooms r ON r.id = b.room_id
            JOIN users u ON u.id = b.guest_user_id
            ORDER BY b.created_at DESC
        ");
        ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'bookings.create':
        Auth::requireRole(['owner', 'admin', 'manager', 'reception']);
        $body = $method === 'POST' ? $_POST : getJsonBody();
        $code = 'BK' . date('ymd') . random_int(1000, 9999);
        $stmt = $pdo->prepare("
            INSERT INTO bookings(booking_code, guest_user_id, room_id, check_in, check_out, adults, children, source, status)
            VALUES (:code,:guest_user_id,:room_id,:check_in,:check_out,:adults,:children,:source,'confirmed')
        ");
        $stmt->execute([
            'code' => $code,
            'guest_user_id' => (int) ($body['guest_user_id'] ?? 0),
            'room_id' => (int) ($body['room_id'] ?? 0),
            'check_in' => $body['check_in'] ?? date('Y-m-d'),
            'check_out' => $body['check_out'] ?? date('Y-m-d', strtotime('+1 day')),
            'adults' => (int) ($body['adults'] ?? 1),
            'children' => (int) ($body['children'] ?? 0),
            'source' => $body['source'] ?? 'Direct',
        ]);
        ApiResponse::json(['ok' => true, 'message' => 'Booking created', 'booking_code' => $code], 201);
        break;

    case 'services.list':
        $stmt = $pdo->query("
            SELECT sr.id, sr.request_type, sr.description, sr.status, sr.priority, r.room_number, u.full_name AS guest_name
            FROM service_requests sr
            LEFT JOIN rooms r ON r.id = sr.room_id
            LEFT JOIN users u ON u.id = sr.guest_user_id
            ORDER BY FIELD(sr.status, 'open', 'in_progress', 'done'), sr.created_at DESC
        ");
        ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'services.create':
        $body = $method === 'POST' ? $_POST : getJsonBody();
        $stmt = $pdo->prepare("
            INSERT INTO service_requests(booking_id, room_id, guest_user_id, request_type, description, priority, status)
            VALUES (:booking_id,:room_id,:guest_user_id,:request_type,:description,:priority,'open')
        ");
        $stmt->execute([
            'booking_id' => (int) ($body['booking_id'] ?? 0),
            'room_id' => (int) ($body['room_id'] ?? 0),
            'guest_user_id' => (int) ($body['guest_user_id'] ?? ($user['id'] ?? 0)),
            'request_type' => $body['request_type'] ?? 'housekeeping',
            'description' => $body['description'] ?? '',
            'priority' => $body['priority'] ?? 'medium',
        ]);
        ApiResponse::json(['ok' => true, 'message' => 'Service request created'], 201);
        break;

    case 'invoices.list':
        $stmt = $pdo->query("
            SELECT i.id, i.invoice_no, i.total_amount, i.payment_status, b.booking_code, u.full_name AS guest_name
            FROM invoices i
            JOIN bookings b ON b.id = i.booking_id
            JOIN users u ON u.id = b.guest_user_id
            ORDER BY i.created_at DESC
        ");
        ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'invoices.download':
        $id = (int) ($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT i.*, b.booking_code, u.full_name, u.email, u.phone, r.room_number
            FROM invoices i
            JOIN bookings b ON b.id = i.booking_id
            JOIN users u ON u.id = b.guest_user_id
            JOIN rooms r ON r.id = b.room_id
            WHERE i.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $invoice = $stmt->fetch();
        if (!$invoice) {
            ApiResponse::json(['ok' => false, 'message' => 'Invoice not found'], 404);
        }
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $invoice['invoice_no'] . '.html');
        echo '<h1>BlueStay HMS Invoice</h1>';
        echo '<p>Invoice: ' . e($invoice['invoice_no']) . '</p>';
        echo '<p>Guest: ' . e($invoice['full_name']) . ' | Room: ' . e($invoice['room_number']) . '</p>';
        echo '<p>Booking: ' . e($invoice['booking_code']) . '</p>';
        echo '<p>Subtotal: ' . number_format((float) $invoice['sub_total'], 2) . '</p>';
        echo '<p>Tax: ' . number_format((float) $invoice['tax_total'], 2) . '</p>';
        echo '<p>Total: ' . number_format((float) $invoice['total_amount'], 2) . '</p>';
        echo '<p>Status: ' . e($invoice['payment_status']) . '</p>';
        exit;

    case 'reports.export':
        Auth::requireRole(['owner', 'admin', 'manager']);
        $stmt = $pdo->query("
            SELECT b.booking_code, u.full_name, r.room_number, i.invoice_no, i.total_amount, i.payment_status
            FROM bookings b
            JOIN users u ON u.id = b.guest_user_id
            JOIN rooms r ON r.id = b.room_id
            LEFT JOIN invoices i ON i.booking_id = b.id
            ORDER BY b.created_at DESC
        ");
        $rows = $stmt->fetchAll();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=hotel_report_' . date('Ymd_His') . '.csv');
        $out = fopen('php://output', 'wb');
        fputcsv($out, ['booking_code', 'guest_name', 'room_number', 'invoice_no', 'total_amount', 'payment_status']);
        foreach ($rows as $r) {
            fputcsv($out, $r);
        }
        fclose($out);
        exit;

    default:
        ApiResponse::json(['ok' => false, 'message' => 'Unknown action'], 404);
}
