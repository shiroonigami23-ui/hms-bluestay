<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';
$title = 'About | BlueStay HMS';
require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="container">
  <section class="card">
    <h1>About BlueStay HMS</h1>
    <p>BlueStay HMS powers premium hospitality operations with smart automation, elegant guest journeys, and real-time department coordination.</p>
    <div class="media-split">
      <img src="assets/img/wiki_1.jpg" alt="Luxury suite room">
      <div>
        <h3>Premium Hotel Experience</h3>
        <p>From reception to housekeeping to customer support, each team gets a dedicated flow so service quality stays consistent at scale.</p>
        <p>Focused on modern hotels with suites, fine dining, pool and lounge facilities, and family/couple travel packages.</p>
        <div class="pill-row">
          <span class="pill">Smart Reception</span>
          <span class="pill">Suite Operations</span>
          <span class="pill">Guest Services</span>
          <span class="pill">Fast Billing</span>
        </div>
      </div>
    </div>
    <div class="grid-3">
      <article><h3>Operations</h3><p>Reservations, check-in/out, room state, housekeeping, and tasking.</p></article>
      <article><h3>Finance</h3><p>GST-ready billing, folio ledger, payment methods, and downloadable invoices.</p></article>
      <article><h3>Growth</h3><p>Customer portal, analytics panels, and clean APIs for mobile/desktop app packaging.</p></article>
    </div>
    <div class="grid-3">
      <article class="feature-card">
        <img src="assets/img/facility_lounge.jpg" alt="Lounge">
        <h3>Lounge & Reception</h3>
        <p>Fast arrivals, concierge workflows, VIP handling and seamless support desk flow.</p>
      </article>
      <article class="feature-card">
        <img src="assets/img/facility_pool.jpg" alt="Pool">
        <h3>Pool & Leisure</h3>
        <p>Facility booking, guest request handling, and service tracking from one place.</p>
      </article>
      <article class="feature-card">
        <img src="assets/img/facility_cuisine.jpg" alt="Cuisine">
        <h3>Cuisine & Dining</h3>
        <p>Kitchen visibility, menu availability, and room service status updates in real-time.</p>
      </article>
    </div>
    <div class="gallery-grid">
      <article class="gallery-item">
        <img src="assets/img/wiki_2.jpg" alt="Hotel exterior">
        <p>Grand Entrance</p>
      </article>
      <article class="gallery-item">
        <img src="assets/img/facility_lounge.jpg" alt="Reception desk zone">
        <p>Reception Zone</p>
      </article>
      <article class="gallery-item">
        <img src="assets/img/facility_pool.jpg" alt="Pool leisure">
        <p>Leisure Deck</p>
      </article>
      <article class="gallery-item">
        <img src="assets/img/facility_cuisine.jpg" alt="Fine dining area">
        <p>Fine Dining</p>
      </article>
    </div>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
