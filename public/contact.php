<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';
$title = 'Contact | BlueStay HMS';
require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="container">
  <section class="card">
    <h1>Contact</h1>
    <div class="media-split">
      <img src="assets/img/facility_lounge.jpg" alt="Reception Lounge">
      <div>
        <h3>Guest Desk & Booking Support</h3>
        <div class="contact-stack">
          <div class="contact-chip"><strong>Email:</strong> shiroonigami23@gamil.com</div>
          <div class="contact-chip"><strong>Phone:</strong> +91 7847948216</div>
          <div class="contact-chip"><strong>Office Hours:</strong> 9 AM - 8 PM IST</div>
        </div>
        <p>For reservations, family/couple packages, suite upgrade requests, and events, contact support directly.</p>
      </div>
    </div>
    <div class="grid-3">
      <article class="quick-card"><h4>Reservation Desk</h4><p>Suite booking, custom package planning, and flexible check-in support.</p></article>
      <article class="quick-card"><h4>Guest Support</h4><p>Service requests, feedback handling, and invoice support post checkout.</p></article>
      <article class="quick-card"><h4>Corporate Desk</h4><p>Business stays, group blocks, and event management coordination.</p></article>
    </div>
    <div class="gallery-grid">
      <article class="gallery-item">
        <img src="assets/img/wiki_2.jpg" alt="Hotel frontage">
        <p>Front Entrance</p>
      </article>
      <article class="gallery-item">
        <img src="assets/img/facility_lounge.jpg" alt="Reception counter">
        <p>Reception Desk</p>
      </article>
      <article class="gallery-item">
        <img src="assets/img/facility_pool.jpg" alt="Pool side">
        <p>Pool Concierge</p>
      </article>
      <article class="gallery-item">
        <img src="assets/img/facility_cuisine.jpg" alt="Dining space">
        <p>Dining Reservations</p>
      </article>
    </div>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
