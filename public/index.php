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
      <p>Bookings, check-in, housekeeping, invoices, cuisine ordering, premium stay packages, and guest self-service on one modern platform.</p>
      <div class="actions">
        <a class="btn" href="register.php">Start Now</a>
        <a class="btn btn-ghost" href="facilities.php">View Facilities</a>
      </div>
      <div class="quick-grid">
        <article class="quick-card"><h4>Luxury Suites</h4><p>Executive, Family, Honeymoon, and Presidential stays.</p></article>
        <article class="quick-card"><h4>Fine Cuisine</h4><p>Indian, Continental, Asian cuisine and in-room dining.</p></article>
        <article class="quick-card"><h4>Signature Facilities</h4><p>Pool, lounge, reception concierge, and premium bar.</p></article>
      </div>
    </div>
    <div class="card panel">
      <img class="hero-image" src="assets/img/wiki_2.jpg" alt="Luxury hotel entrance">
      <h3>Guest & Hotel Services</h3>
      <ul>
        <li>Fast check-in and easy digital reservations</li>
        <li>Real-time housekeeping and room status visibility</li>
        <li>Invoice and payment history for every stay</li>
        <li>Smooth concierge support across departments</li>
      </ul>
      <div class="pill-row">
        <span class="pill">Reception</span>
        <span class="pill">Pool</span>
        <span class="pill">Lounge</span>
        <span class="pill">Cuisine</span>
        <span class="pill">Premium Suites</span>
      </div>
    </div>
  </section>

  <section class="container card">
    <h2 class="section-title">Stay Packages</h2>
    <div class="grid-3">
      <article class="feature-card">
        <img src="assets/img/suite_couple.jpg" alt="Couple suite">
        <h3>Couple Escape</h3>
        <p>Rose decor, candle-light dinner, spa voucher, breakfast in bed.</p>
      </article>
      <article class="feature-card">
        <img src="assets/img/suite_family.jpg" alt="Family package">
        <h3>Family Fun</h3>
        <p>Connected rooms, kids menu, pool access, city transfer support.</p>
      </article>
      <article class="feature-card">
        <img src="assets/img/facility_reception.jpg" alt="Business package">
        <h3>Business Elite</h3>
        <p>Meeting lounge access, premium Wi-Fi, executive breakfast, fast checkout.</p>
      </article>
    </div>
  </section>

  <section class="container card">
    <h2 class="section-title">Spaces You Will Love</h2>
    <div class="gallery-grid">
      <article class="gallery-item">
        <img src="assets/img/facility_lounge.jpg" alt="Reception lounge">
        <p>Reception Lounge</p>
      </article>
      <article class="gallery-item">
        <img src="assets/img/facility_pool.jpg" alt="Swimming pool area">
        <p>Swimming Pool</p>
      </article>
      <article class="gallery-item">
        <img src="assets/img/cuisine_indian.jpg" alt="Indian cuisine">
        <p>Indian Cuisine</p>
      </article>
      <article class="gallery-item">
        <img src="assets/img/cuisine_continental.jpg" alt="Continental cuisine">
        <p>Continental Cuisine</p>
      </article>
      <article class="gallery-item">
        <img src="assets/img/cuisine_asian.jpg" alt="Asian cuisine">
        <p>Asian Cuisine</p>
      </article>
      <article class="gallery-item">
        <img src="assets/img/facility_bar.jpg" alt="Sky bar">
        <p>Sky Bar</p>
      </article>
    </div>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
