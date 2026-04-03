<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';
$title = 'Help | BlueStay HMS';
require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="container">
  <section class="card">
    <h1>Help Center</h1>
    <div class="faq">
      <article><h3>How to check in a guest?</h3><p>Login as reception or manager and create a booking from dashboard cards.</p></article>
      <article><h3>How to download invoice?</h3><p>Open invoice list in dashboard and click Download.</p></article>
      <article><h3>How to add rooms/items?</h3><p>Admin and manager roles can add and update room records via API-enabled cards.</p></article>
      <article><h3>How to raise a service request?</h3><p>Use service API endpoint or customer flow in dashboard module.</p></article>
    </div>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
