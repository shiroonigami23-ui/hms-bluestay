<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';
$title = 'Terms | BlueStay HMS';
require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="container">
  <section class="card legal">
    <h1>Terms of Use</h1>
    <p>This software is provided for internal hotel operations. Authorized users must protect credentials and avoid unauthorized access to guest data.</p>
    <p>Billing and compliance data must be verified by hotel management before legal submission.</p>
    <p>By using this system, your organization accepts responsibility for local law compliance and privacy obligations.</p>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
