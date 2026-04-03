<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';
$title = 'Privacy | BlueStay HMS';
require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="container">
  <section class="card legal">
    <h1>Privacy Policy</h1>
    <p>BlueStay HMS stores guest and staff data only for hotel operations such as reservations, billing, and service delivery.</p>
    <p>Sensitive data should be handled using strong passwords, HTTPS in production, and restricted role access.</p>
    <p>Operators should implement regular backups, retention policies, and breach response procedures.</p>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
