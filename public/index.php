<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';
$title = 'BlueStay HMS | Smart Hotel Operations';
require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="hero-wrap">
  <section class="hero">
    <div>
      <p class="tag">Built from your HMS roadmap</p>
      <h1>All-In-One Hotel Management System</h1>
      <p>Bookings, check-in, housekeeping, invoices, staff operations, and customer self-service on one responsive platform.</p>
      <div class="actions">
        <a class="btn" href="register.php">Start Now</a>
        <a class="btn btn-ghost" href="about.php">Explore Features</a>
      </div>
    </div>
    <div class="card panel">
      <h3>Live Modules</h3>
      <ul>
        <li>Role-based dashboards for 8 user types</li>
        <li>Invoice download and payment tracking</li>
        <li>Room/service request management</li>
        <li>API-ready architecture for app wrappers</li>
      </ul>
    </div>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
