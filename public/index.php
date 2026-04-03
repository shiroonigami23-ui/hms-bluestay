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
      <h3>Live Modules</h3>
      <ul>
        <li>Role-based dashboards for 8 user types</li>
        <li>Invoice download and payment tracking</li>
        <li>Room/service request management</li>
        <li>API-ready architecture for app wrappers</li>
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
        <img src="assets/img/wiki_1.jpg" alt="Couple suite">
        <h3>Couple Escape</h3>
        <p>Rose decor, candle-light dinner, spa voucher, breakfast in bed.</p>
      </article>
      <article class="feature-card">
        <img src="assets/img/facility_pool.jpg" alt="Family package">
        <h3>Family Fun</h3>
        <p>Connected rooms, kids menu, pool access, city transfer support.</p>
      </article>
      <article class="feature-card">
        <img src="assets/img/facility_cuisine.jpg" alt="Business package">
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
        <img src="assets/img/facility_cuisine.jpg" alt="Restaurant cuisine">
        <p>Cuisine & Dining</p>
      </article>
      <article class="gallery-item">
        <img src="assets/img/wiki_2.jpg" alt="Evening hotel exterior">
        <p>Premium Arrival</p>
      </article>
    </div>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
