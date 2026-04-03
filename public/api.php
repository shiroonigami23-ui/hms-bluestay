<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$user = Auth::user();
$publicActions = ['auth.ping'];

if (!in_array($action, $publicActions, true) && !Auth::check()) {
    ApiResponse::json(['ok' => false, 'message' => 'Unauthorized'], 401);
}

function request_data(string $method): array
{
    if ($method === 'POST') {
        return $_POST;
    }
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    $parsed = json_decode($raw, true);
    return is_array($parsed) ? $parsed : [];
}

function must_have(array $data, array $fields): void
{
    foreach ($fields as $field) {
        if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
            ApiResponse::json(['ok' => false, 'message' => "Missing field: {$field}"], 422);
        }
    }
}

try {
    switch ($action) {
        case 'auth.ping':
            ApiResponse::json(['ok' => true, 'app' => 'BlueStay HMS']);
            break;

        case 'auth.me':
            ApiResponse::json(['ok' => true, 'data' => Auth::user()]);
            break;

        case 'dashboard.stats':
            $stmt = $pdo->query("
                SELECT
                  (SELECT COUNT(*) FROM users) AS users,
                  (SELECT COUNT(*) FROM rooms) AS rooms,
                  (SELECT COUNT(*) FROM bookings) AS bookings,
                  (SELECT COUNT(*) FROM service_requests WHERE status IN ('open','in_progress')) AS open_requests,
                  (SELECT IFNULL(SUM(total_amount), 0) FROM invoices) AS total_revenue,
                  (SELECT IFNULL(SUM(amount), 0) FROM payments WHERE payment_status = 'success') AS collected_amount
            ");
            ApiResponse::json(['ok' => true, 'data' => $stmt->fetch()]);
            break;

        case 'users.list':
            Auth::requireRole(['owner', 'admin', 'manager']);
            $stmt = $pdo->query("SELECT id, full_name, email, phone, role, created_at FROM users ORDER BY id DESC");
            ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'rooms.list':
            $stmt = $pdo->query("SELECT id, room_number, floor_no, room_type, status, base_rate FROM rooms ORDER BY floor_no, room_number");
            ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'rooms.create':
            Auth::requireRole(['owner', 'admin', 'manager']);
            $body = request_data($method);
            must_have($body, ['room_number', 'floor_no', 'room_type', 'base_rate']);
            $stmt = $pdo->prepare("INSERT INTO rooms(room_number,floor_no,room_type,status,base_rate) VALUES(:room_number,:floor_no,:room_type,:status,:base_rate)");
            $stmt->execute([
                'room_number' => (string) $body['room_number'],
                'floor_no' => (int) $body['floor_no'],
                'room_type' => (string) $body['room_type'],
                'status' => $body['status'] ?? 'available',
                'base_rate' => (float) $body['base_rate'],
            ]);
            ApiResponse::json(['ok' => true, 'message' => 'Room created', 'id' => (int) $pdo->lastInsertId()], 201);
            break;

        case 'rooms.update':
            Auth::requireRole(['owner', 'admin', 'manager']);
            $body = request_data($method);
            must_have($body, ['id', 'room_number', 'floor_no', 'room_type', 'base_rate']);
            $stmt = $pdo->prepare("UPDATE rooms SET room_number=:room_number,floor_no=:floor_no,room_type=:room_type,status=:status,base_rate=:base_rate WHERE id=:id");
            $stmt->execute([
                'id' => (int) $body['id'],
                'room_number' => (string) $body['room_number'],
                'floor_no' => (int) $body['floor_no'],
                'room_type' => (string) $body['room_type'],
                'status' => $body['status'] ?? 'available',
                'base_rate' => (float) $body['base_rate'],
            ]);
            ApiResponse::json(['ok' => true, 'message' => 'Room updated']);
            break;

        case 'rooms.delete':
            Auth::requireRole(['owner', 'admin']);
            $body = request_data($method);
            must_have($body, ['id']);
            $stmt = $pdo->prepare("DELETE FROM rooms WHERE id=:id");
            $stmt->execute(['id' => (int) $body['id']]);
            ApiResponse::json(['ok' => true, 'message' => 'Room deleted']);
            break;

        case 'rooms.updateStatus':
            Auth::requireRole(['owner', 'admin', 'manager', 'reception', 'housekeeping']);
            $body = request_data($method);
            must_have($body, ['id', 'status']);
            $stmt = $pdo->prepare("UPDATE rooms SET status = :status WHERE id = :id");
            $stmt->execute(['status' => (string) $body['status'], 'id' => (int) $body['id']]);
            ApiResponse::json(['ok' => true, 'message' => 'Room status updated']);
            break;

        case 'bookings.list':
            $stmt = $pdo->query("
                SELECT b.id, b.booking_code, b.status, b.check_in, b.check_out, b.adults, b.children, b.source, r.room_number, u.full_name AS guest_name
                FROM bookings b
                JOIN rooms r ON r.id = b.room_id
                JOIN users u ON u.id = b.guest_user_id
                ORDER BY b.created_at DESC
            ");
            ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'bookings.get':
            $id = (int) ($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT b.*, r.room_number, u.full_name AS guest_name, u.email, u.phone
                FROM bookings b
                JOIN rooms r ON r.id = b.room_id
                JOIN users u ON u.id = b.guest_user_id
                WHERE b.id = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if (!$row) {
                ApiResponse::json(['ok' => false, 'message' => 'Booking not found'], 404);
            }
            ApiResponse::json(['ok' => true, 'data' => $row]);
            break;

        case 'bookings.create':
            Auth::requireRole(['owner', 'admin', 'manager', 'reception']);
            $body = request_data($method);
            must_have($body, ['guest_user_id', 'room_id', 'check_in', 'check_out']);
            $code = 'BK' . date('ymd') . random_int(1000, 9999);
            $stmt = $pdo->prepare("
                INSERT INTO bookings(booking_code, guest_user_id, room_id, check_in, check_out, adults, children, source, status)
                VALUES (:code,:guest_user_id,:room_id,:check_in,:check_out,:adults,:children,:source,'confirmed')
            ");
            $stmt->execute([
                'code' => $code,
                'guest_user_id' => (int) $body['guest_user_id'],
                'room_id' => (int) $body['room_id'],
                'check_in' => (string) $body['check_in'],
                'check_out' => (string) $body['check_out'],
                'adults' => (int) ($body['adults'] ?? 1),
                'children' => (int) ($body['children'] ?? 0),
                'source' => (string) ($body['source'] ?? 'Direct'),
            ]);
            ApiResponse::json(['ok' => true, 'message' => 'Booking created', 'booking_code' => $code], 201);
            break;

        case 'bookings.updateStatus':
        case 'bookings.checkin':
        case 'bookings.checkout':
            Auth::requireRole(['owner', 'admin', 'manager', 'reception']);
            $body = request_data($method);
            must_have($body, ['id']);
            $status = $action === 'bookings.checkin' ? 'checked_in' : ($action === 'bookings.checkout' ? 'checked_out' : ($body['status'] ?? 'confirmed'));
            $stmt = $pdo->prepare("UPDATE bookings SET status=:status WHERE id=:id");
            $stmt->execute(['status' => $status, 'id' => (int) $body['id']]);
            ApiResponse::json(['ok' => true, 'message' => 'Booking status updated', 'status' => $status]);
            break;

        case 'tasks.list':
            Auth::requireRole(['owner', 'admin', 'manager', 'housekeeping', 'reception']);
            $stmt = $pdo->query("
                SELECT t.id, t.task_type, t.priority, t.status, t.created_at, r.room_number, u.full_name AS assigned_to
                FROM housekeeping_tasks t
                JOIN rooms r ON r.id = t.room_id
                LEFT JOIN users u ON u.id = t.assigned_to_user_id
                ORDER BY FIELD(t.status, 'pending','in_progress','done'), t.created_at DESC
            ");
            ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'tasks.create':
            Auth::requireRole(['owner', 'admin', 'manager', 'reception']);
            $body = request_data($method);
            must_have($body, ['room_id', 'task_type']);
            $stmt = $pdo->prepare("
                INSERT INTO housekeeping_tasks(room_id,assigned_to_user_id,task_type,priority,status)
                VALUES (:room_id,:assigned_to_user_id,:task_type,:priority,'pending')
            ");
            $stmt->execute([
                'room_id' => (int) $body['room_id'],
                'assigned_to_user_id' => isset($body['assigned_to_user_id']) ? (int) $body['assigned_to_user_id'] : null,
                'task_type' => (string) $body['task_type'],
                'priority' => (string) ($body['priority'] ?? 'medium'),
            ]);
            ApiResponse::json(['ok' => true, 'message' => 'Task created', 'id' => (int) $pdo->lastInsertId()], 201);
            break;

        case 'tasks.updateStatus':
            Auth::requireRole(['owner', 'admin', 'manager', 'housekeeping']);
            $body = request_data($method);
            must_have($body, ['id', 'status']);
            $stmt = $pdo->prepare("UPDATE housekeeping_tasks SET status=:status WHERE id=:id");
            $stmt->execute(['id' => (int) $body['id'], 'status' => (string) $body['status']]);
            ApiResponse::json(['ok' => true, 'message' => 'Task status updated']);
            break;

        case 'services.list':
            $stmt = $pdo->query("
                SELECT sr.id, sr.request_type, sr.description, sr.status, sr.priority, sr.created_at, r.room_number, u.full_name AS guest_name
                FROM service_requests sr
                LEFT JOIN rooms r ON r.id = sr.room_id
                LEFT JOIN users u ON u.id = sr.guest_user_id
                ORDER BY FIELD(sr.status, 'open','in_progress','done'), sr.created_at DESC
            ");
            ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'services.create':
            $body = request_data($method);
            must_have($body, ['request_type', 'description']);
            $stmt = $pdo->prepare("
                INSERT INTO service_requests(booking_id, room_id, guest_user_id, request_type, description, priority, status)
                VALUES (:booking_id,:room_id,:guest_user_id,:request_type,:description,:priority,'open')
            ");
            $stmt->execute([
                'booking_id' => isset($body['booking_id']) ? (int) $body['booking_id'] : null,
                'room_id' => isset($body['room_id']) ? (int) $body['room_id'] : null,
                'guest_user_id' => isset($body['guest_user_id']) ? (int) $body['guest_user_id'] : (int) ($user['id'] ?? 0),
                'request_type' => (string) $body['request_type'],
                'description' => (string) $body['description'],
                'priority' => (string) ($body['priority'] ?? 'medium'),
            ]);
            ApiResponse::json(['ok' => true, 'message' => 'Service request created', 'id' => (int) $pdo->lastInsertId()], 201);
            break;

        case 'services.updateStatus':
            Auth::requireRole(['owner', 'admin', 'manager', 'reception', 'housekeeping', 'kitchen']);
            $body = request_data($method);
            must_have($body, ['id', 'status']);
            $stmt = $pdo->prepare("UPDATE service_requests SET status=:status WHERE id=:id");
            $stmt->execute(['id' => (int) $body['id'], 'status' => (string) $body['status']]);
            ApiResponse::json(['ok' => true, 'message' => 'Service request status updated']);
            break;

        case 'inventory.list':
            Auth::requireRole(['owner', 'admin', 'manager', 'housekeeping', 'kitchen']);
            $stmt = $pdo->query("SELECT * FROM inventory_items ORDER BY category, item_name");
            ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'inventory.create':
            Auth::requireRole(['owner', 'admin', 'manager']);
            $body = request_data($method);
            must_have($body, ['item_name', 'category', 'unit', 'stock_qty', 'cost_price']);
            $stmt = $pdo->prepare("
                INSERT INTO inventory_items(item_name,category,unit,stock_qty,reorder_level,cost_price)
                VALUES (:item_name,:category,:unit,:stock_qty,:reorder_level,:cost_price)
            ");
            $stmt->execute([
                'item_name' => (string) $body['item_name'],
                'category' => (string) $body['category'],
                'unit' => (string) $body['unit'],
                'stock_qty' => (float) $body['stock_qty'],
                'reorder_level' => (float) ($body['reorder_level'] ?? 0),
                'cost_price' => (float) $body['cost_price'],
            ]);
            ApiResponse::json(['ok' => true, 'message' => 'Inventory item created', 'id' => (int) $pdo->lastInsertId()], 201);
            break;

        case 'inventory.updateStock':
            Auth::requireRole(['owner', 'admin', 'manager', 'housekeeping', 'kitchen']);
            $body = request_data($method);
            must_have($body, ['id', 'stock_qty']);
            $stmt = $pdo->prepare("UPDATE inventory_items SET stock_qty=:stock_qty, updated_at=NOW() WHERE id=:id");
            $stmt->execute(['id' => (int) $body['id'], 'stock_qty' => (float) $body['stock_qty']]);
            ApiResponse::json(['ok' => true, 'message' => 'Stock updated']);
            break;

        case 'fnb.menu.list':
            Auth::requireRole(['owner', 'admin', 'manager', 'kitchen', 'customer', 'reception']);
            $stmt = $pdo->query("SELECT * FROM menu_items ORDER BY category, item_name");
            ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'fnb.menu.create':
            Auth::requireRole(['owner', 'admin', 'manager', 'kitchen']);
            $body = request_data($method);
            must_have($body, ['item_name', 'category', 'price']);
            $stmt = $pdo->prepare("INSERT INTO menu_items(item_name,category,price,is_available) VALUES(:item_name,:category,:price,:is_available)");
            $stmt->execute([
                'item_name' => (string) $body['item_name'],
                'category' => (string) $body['category'],
                'price' => (float) $body['price'],
                'is_available' => isset($body['is_available']) ? (int) $body['is_available'] : 1,
            ]);
            ApiResponse::json(['ok' => true, 'message' => 'Menu item created', 'id' => (int) $pdo->lastInsertId()], 201);
            break;

        case 'fnb.menu.updateAvailability':
            Auth::requireRole(['owner', 'admin', 'manager', 'kitchen']);
            $body = request_data($method);
            must_have($body, ['id', 'is_available']);
            $stmt = $pdo->prepare("UPDATE menu_items SET is_available=:is_available WHERE id=:id");
            $stmt->execute(['id' => (int) $body['id'], 'is_available' => (int) $body['is_available']]);
            ApiResponse::json(['ok' => true, 'message' => 'Menu availability updated']);
            break;

        case 'security.visitors.list':
            Auth::requireRole(['owner', 'admin', 'manager', 'security', 'reception']);
            $stmt = $pdo->query("
                SELECT v.id, v.visitor_name, v.phone, v.vehicle_no, v.purpose, v.check_in_at, v.check_out_at, u.full_name AS logged_by
                FROM visitor_logs v
                LEFT JOIN users u ON u.id = v.logged_by_user_id
                ORDER BY v.check_in_at DESC
            ");
            ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'security.visitors.create':
            Auth::requireRole(['owner', 'admin', 'manager', 'security', 'reception']);
            $body = request_data($method);
            must_have($body, ['visitor_name', 'purpose']);
            $stmt = $pdo->prepare("
                INSERT INTO visitor_logs(visitor_name,phone,vehicle_no,purpose,check_in_at,logged_by_user_id)
                VALUES (:visitor_name,:phone,:vehicle_no,:purpose,NOW(),:logged_by_user_id)
            ");
            $stmt->execute([
                'visitor_name' => (string) $body['visitor_name'],
                'phone' => $body['phone'] ?? null,
                'vehicle_no' => $body['vehicle_no'] ?? null,
                'purpose' => (string) $body['purpose'],
                'logged_by_user_id' => (int) ($user['id'] ?? 0),
            ]);
            ApiResponse::json(['ok' => true, 'message' => 'Visitor logged', 'id' => (int) $pdo->lastInsertId()], 201);
            break;

        case 'security.visitors.checkout':
            Auth::requireRole(['owner', 'admin', 'manager', 'security', 'reception']);
            $body = request_data($method);
            must_have($body, ['id']);
            $stmt = $pdo->prepare("UPDATE visitor_logs SET check_out_at=NOW() WHERE id=:id");
            $stmt->execute(['id' => (int) $body['id']]);
            ApiResponse::json(['ok' => true, 'message' => 'Visitor checkout recorded']);
            break;

        case 'invoices.list':
            $stmt = $pdo->query("
                SELECT i.id, i.invoice_no, i.total_amount, i.payment_status, i.created_at, b.booking_code, u.full_name AS guest_name
                FROM invoices i
                JOIN bookings b ON b.id = i.booking_id
                JOIN users u ON u.id = b.guest_user_id
                ORDER BY i.created_at DESC
            ");
            ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'invoices.generate':
            Auth::requireRole(['owner', 'admin', 'manager', 'reception']);
            $body = request_data($method);
            must_have($body, ['booking_id', 'sub_total']);
            $subTotal = (float) $body['sub_total'];
            $taxTotal = isset($body['tax_total']) ? (float) $body['tax_total'] : round($subTotal * 0.18, 2);
            $total = $subTotal + $taxTotal;
            $invoiceNo = 'INV-' . date('ymd') . '-' . random_int(100, 999);
            $stmt = $pdo->prepare("
                INSERT INTO invoices(invoice_no,booking_id,gstin,sub_total,tax_total,total_amount,payment_status)
                VALUES (:invoice_no,:booking_id,:gstin,:sub_total,:tax_total,:total_amount,'unpaid')
            ");
            $stmt->execute([
                'invoice_no' => $invoiceNo,
                'booking_id' => (int) $body['booking_id'],
                'gstin' => $body['gstin'] ?? null,
                'sub_total' => $subTotal,
                'tax_total' => $taxTotal,
                'total_amount' => $total,
            ]);
            ApiResponse::json(['ok' => true, 'message' => 'Invoice generated', 'invoice_no' => $invoiceNo, 'id' => (int) $pdo->lastInsertId()], 201);
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

        case 'payments.list':
            Auth::requireRole(['owner', 'admin', 'manager', 'reception']);
            $stmt = $pdo->query("
                SELECT p.id, p.invoice_id, p.method, p.amount, p.transaction_ref, p.payment_status, p.paid_at, i.invoice_no
                FROM payments p
                JOIN invoices i ON i.id = p.invoice_id
                ORDER BY p.created_at DESC
            ");
            ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'payments.create':
            Auth::requireRole(['owner', 'admin', 'manager', 'reception']);
            $body = request_data($method);
            must_have($body, ['invoice_id', 'method', 'amount']);
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
                INSERT INTO payments(invoice_id,method,amount,transaction_ref,payment_status,paid_at)
                VALUES (:invoice_id,:method,:amount,:transaction_ref,:payment_status,:paid_at)
            ");
            $status = (string) ($body['payment_status'] ?? 'success');
            $stmt->execute([
                'invoice_id' => (int) $body['invoice_id'],
                'method' => (string) $body['method'],
                'amount' => (float) $body['amount'],
                'transaction_ref' => $body['transaction_ref'] ?? null,
                'payment_status' => $status,
                'paid_at' => $status === 'success' ? date('Y-m-d H:i:s') : null,
            ]);

            $invoiceStmt = $pdo->prepare("
                UPDATE invoices i
                JOIN (
                    SELECT invoice_id, IFNULL(SUM(CASE WHEN payment_status='success' THEN amount ELSE 0 END),0) AS paid
                    FROM payments
                    WHERE invoice_id = :invoice_id
                    GROUP BY invoice_id
                ) p ON p.invoice_id = i.id
                SET i.payment_status = CASE
                    WHEN p.paid >= i.total_amount THEN 'paid'
                    WHEN p.paid > 0 THEN 'partial'
                    ELSE 'unpaid'
                END
                WHERE i.id = :invoice_id2
            ");
            $invoiceStmt->execute([
                'invoice_id' => (int) $body['invoice_id'],
                'invoice_id2' => (int) $body['invoice_id'],
            ]);
            $pdo->commit();
            ApiResponse::json(['ok' => true, 'message' => 'Payment recorded', 'id' => (int) $pdo->lastInsertId()], 201);
            break;

        case 'reports.summary':
            Auth::requireRole(['owner', 'admin', 'manager']);
            $stmt = $pdo->query("
                SELECT
                    DATE(b.check_in) AS date_label,
                    COUNT(b.id) AS bookings_count,
                    IFNULL(SUM(i.total_amount),0) AS invoiced
                FROM bookings b
                LEFT JOIN invoices i ON i.booking_id = b.id
                GROUP BY DATE(b.check_in)
                ORDER BY DATE(b.check_in) DESC
                LIMIT 30
            ");
            ApiResponse::json(['ok' => true, 'data' => $stmt->fetchAll()]);
            break;

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
} catch (Throwable $e) {
    error_log('[API ERROR] ' . $e->getMessage());
    ApiResponse::json(['ok' => false, 'message' => 'API request failed safely', 'detail' => $e->getMessage()], 500);
}
