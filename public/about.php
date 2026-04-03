<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';
$title = 'About | BlueStay HMS';
require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="container">
  <section class="card">
    <h1>About BlueStay HMS</h1>
    <p>BlueStay HMS is a scalable hospitality platform designed for hotels that need modern operations, compliance support, and fast guest service.</p>
    <div class="grid-3">
      <article><h3>Operations</h3><p>Reservations, check-in/out, room state, housekeeping, and tasking.</p></article>
      <article><h3>Finance</h3><p>GST-ready billing, folio ledger, payment methods, and downloadable invoices.</p></article>
      <article><h3>Growth</h3><p>Customer portal, analytics panels, and clean APIs for mobile/desktop app packaging.</p></article>
    </div>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
