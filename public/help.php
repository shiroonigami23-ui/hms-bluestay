<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';
$title = 'Help | BlueStay HMS';
require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="container">
  <section class="card">
    <h1>Help Center (Customer)</h1>
    <div class="media-split">
      <img src="assets/img/facility_cuisine.jpg" alt="Customer help">
      <div>
        <h3>For Guests & Customers</h3>
        <p>This section is designed for customers staying at the hotel. Staff operations are managed via role-based dashboards.</p>
        <p>Use these quick answers for booking help, service requests, and invoice download.</p>
        <div class="pill-row">
          <span class="pill">Check-in Support</span>
          <span class="pill">Invoice Help</span>
          <span class="pill">Service Requests</span>
          <span class="pill">Package Queries</span>
        </div>
      </div>
    </div>
    <div class="faq">
      <article><h3>How to check in a guest?</h3><p>Login as reception or manager and create a booking from dashboard cards.</p></article>
      <article><h3>How to download invoice?</h3><p>Open invoice list in dashboard and click Download.</p></article>
      <article><h3>How to add rooms/items?</h3><p>Admin and manager roles can add and update room records via API-enabled cards.</p></article>
      <article><h3>How to raise a service request?</h3><p>Use service API endpoint or customer flow in dashboard module.</p></article>
    </div>
    <div class="grid-3">
      <article class="quick-card"><h4>Customer Chat Desk</h4><p>Fast replies for booking updates, suite changes, and stay extensions.</p></article>
      <article class="quick-card"><h4>Invoice & Payment</h4><p>Get invoice copies, payment confirmations, and bill clarifications quickly.</p></article>
      <article class="quick-card"><h4>On-Stay Assistance</h4><p>Request housekeeping, food delivery, pool timings, or front-desk support.</p></article>
    </div>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
