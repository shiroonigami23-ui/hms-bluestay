<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';
Auth::requireLogin();

$user = Auth::user();
$role = $user['role'];
$title = dashboard_title($role) . ' | BlueStay HMS';

$statsStmt = $pdo->query("
    SELECT
      (SELECT COUNT(*) FROM rooms) AS rooms_total,
      (SELECT COUNT(*) FROM rooms WHERE status = 'available') AS rooms_available,
      (SELECT COUNT(*) FROM bookings WHERE DATE(check_in) = CURDATE()) AS arrivals_today,
      (SELECT IFNULL(SUM(total_amount), 0) FROM invoices WHERE DATE(created_at) = CURDATE()) AS revenue_today
");
$stats = $statsStmt->fetch() ?: [];

$bookings = $pdo->query("
    SELECT b.id, b.booking_code, b.status, b.check_in, b.check_out, u.full_name AS guest_name, r.room_number
    FROM bookings b
    JOIN users u ON u.id = b.guest_user_id
    JOIN rooms r ON r.id = b.room_id
    ORDER BY b.created_at DESC
    LIMIT 8
")->fetchAll();

$tasks = $pdo->query("
    SELECT t.id, t.task_type, t.priority, t.status, r.room_number
    FROM housekeeping_tasks t
    JOIN rooms r ON r.id = t.room_id
    ORDER BY FIELD(t.status, 'pending', 'in_progress', 'done'), t.created_at DESC
    LIMIT 8
")->fetchAll();

$invoices = $pdo->query("
    SELECT i.id, i.invoice_no, i.total_amount, i.payment_status, b.booking_code
    FROM invoices i
    JOIN bookings b ON b.id = i.booking_id
    ORDER BY i.created_at DESC
    LIMIT 8
")->fetchAll();

require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="dashboard-layout">
  <aside class="sidebar" id="sidebar">
    <h3><?= e(dashboard_title($role)) ?></h3>
    <a href="#stats" class="nav-pill">Overview</a>
    <a href="#bookings" class="nav-pill">Bookings</a>
    <a href="#tasks" class="nav-pill">Tasks</a>
    <a href="#invoices" class="nav-pill">Invoices</a>
    <a href="api.php?action=reports.export">Download Report</a>
  </aside>

  <section class="dashboard-content">
    <div class="card" id="stats">
      <h2>Welcome, <?= e($user['full_name']) ?></h2>
      <div class="stats-grid">
        <div><p>Total Rooms</p><strong><?= e((string) $stats['rooms_total']) ?></strong></div>
        <div><p>Available</p><strong><?= e((string) $stats['rooms_available']) ?></strong></div>
        <div><p>Arrivals Today</p><strong><?= e((string) $stats['arrivals_today']) ?></strong></div>
        <div><p>Revenue Today</p><strong>INR <?= number_format((float) ($stats['revenue_today'] ?? 0), 2) ?></strong></div>
      </div>
    </div>

    <div class="card">
      <div class="tab-row">
        <button class="tab-btn active" data-tab="bookings" type="button"><img src="assets/img/icon-booking.svg" alt="">Bookings</button>
        <button class="tab-btn" data-tab="tasks" type="button"><img src="assets/img/icon-task.svg" alt="">Tasks</button>
        <button class="tab-btn" data-tab="invoices" type="button"><img src="assets/img/icon-invoice.svg" alt="">Invoices</button>
      </div>
    </div>

    <div class="card tab-panel active" id="bookings">
      <h3>Latest Bookings</h3>
      <table>
        <tr><th>Code</th><th>Guest</th><th>Room</th><th>Status</th></tr>
        <?php foreach ($bookings as $b): ?>
          <tr>
            <td><?= e($b['booking_code']) ?></td>
            <td><?= e($b['guest_name']) ?></td>
            <td><?= e($b['room_number']) ?></td>
            <td><?= e($b['status']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <div class="card tab-panel" id="tasks">
      <h3>Housekeeping Tasks</h3>
      <table>
        <tr><th>Room</th><th>Task</th><th>Priority</th><th>Status</th></tr>
        <?php foreach ($tasks as $t): ?>
          <tr>
            <td><?= e($t['room_number']) ?></td>
            <td><?= e($t['task_type']) ?></td>
            <td><?= e($t['priority']) ?></td>
            <td><?= e($t['status']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <div class="card tab-panel" id="invoices">
      <h3>Invoices</h3>
      <table>
        <tr><th>No</th><th>Booking</th><th>Total</th><th>Status</th><th>Download</th></tr>
        <?php foreach ($invoices as $i): ?>
          <tr>
            <td><?= e($i['invoice_no']) ?></td>
            <td><?= e($i['booking_code']) ?></td>
            <td>INR <?= number_format((float) $i['total_amount'], 2) ?></td>
            <td><?= e($i['payment_status']) ?></td>
            <td><a href="api.php?action=invoices.download&id=<?= (int) $i['id'] ?>">Download</a></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <div class="card info-cards">
      <article class="mini-card">
        <h4>Quick API</h4>
        <p>Use endpoint-based integrations for mobile and desktop wrappers.</p>
      </article>
      <article class="mini-card">
        <h4>Invoice Ready</h4>
        <p>Downloadable invoice flow is connected from dashboard and API.</p>
      </article>
      <article class="mini-card">
        <h4>Scale Ready</h4>
        <p>Schema uses indexed joins and structured role access for growth.</p>
      </article>
    </div>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
