<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';
$title = 'Facilities | BlueStay HMS';
require dirname(__DIR__) . '/app/views/partials/header.php';
?>
<main class="container">
  <section class="card">
    <h1>Facilities & Experience</h1>
    <p>Explore our premium offerings crafted for couples, families, and business guests.</p>

    <div class="tab-row">
      <button class="tab-btn active" data-tab="fac-suites" type="button">Suites</button>
      <button class="tab-btn" data-tab="fac-cuisine" type="button">Cuisine</button>
      <button class="tab-btn" data-tab="fac-leisure" type="button">Leisure</button>
      <button class="tab-btn" data-tab="fac-packages" type="button">Packages</button>
    </div>

    <div class="tab-panel active" id="fac-suites">
      <div class="grid-3">
        <article class="feature-card">
          <img src="assets/img/wiki_1.jpg" alt="Executive Suite">
          <h3>Executive Suite</h3>
          <p>Business-friendly space with work lounge and premium amenities.</p>
        </article>
        <article class="feature-card">
          <img src="assets/img/suite_family.jpg" alt="Family Suite">
          <h3>Family Suite</h3>
          <p>Comfort-first layout with extra space for long family stays.</p>
        </article>
        <article class="feature-card">
          <img src="assets/img/suite_couple.jpg" alt="Honeymoon Suite">
          <h3>Honeymoon Suite</h3>
          <p>Elegant interiors, cozy lighting, and curated romantic setup.</p>
        </article>
      </div>
    </div>

    <div class="tab-panel" id="fac-cuisine">
      <div class="grid-3">
        <article class="feature-card">
          <img src="assets/img/cuisine_indian.jpg" alt="Indian Cuisine">
          <h3>Indian Cuisine</h3>
          <p>Regional classics, chef specials, and curated seasonal menu.</p>
        </article>
        <article class="feature-card">
          <img src="assets/img/cuisine_continental.jpg" alt="Continental Cuisine">
          <h3>Continental</h3>
          <p>Global comfort dishes for breakfast, lunch, and evening dining.</p>
        </article>
        <article class="feature-card">
          <img src="assets/img/cuisine_asian.jpg" alt="Asian Cuisine">
          <h3>Asian Cuisine</h3>
          <p>Fresh Asian flavors with balanced menu options for every guest profile.</p>
        </article>
      </div>
    </div>

    <div class="tab-panel" id="fac-leisure">
      <div class="grid-3">
        <article class="feature-card">
          <img src="assets/img/facility_pool.jpg" alt="Swimming Pool">
          <h3>Swimming Pool</h3>
          <p>Relaxed poolside zone with service tracking and guest support.</p>
        </article>
        <article class="feature-card">
          <img src="assets/img/facility_lounge.jpg" alt="Lounge">
          <h3>Lounge</h3>
          <p>Stylish lobby lounge for meetings, casual gatherings, and coffee.</p>
        </article>
        <article class="feature-card">
          <img src="assets/img/facility_reception.jpg" alt="Reception">
          <h3>Reception & Concierge</h3>
          <p>Fast check-in desk, concierge help, and premium guest handling.</p>
        </article>
        <article class="feature-card">
          <img src="assets/img/facility_bar.jpg" alt="Bar and beverages">
          <h3>Sky Bar</h3>
          <p>Evening beverages, curated mocktails, and social seating with live service.</p>
        </article>
        <article class="feature-card">
          <img src="assets/img/wiki_2.jpg" alt="Arrival lobby">
          <h3>Arrival Lobby</h3>
          <p>Grand arrival zone with quick guest verification and support routing.</p>
        </article>
        <article class="feature-card">
          <img src="assets/img/wiki_1.jpg" alt="Premium suite hallway">
          <h3>Suite Wing</h3>
          <p>Dedicated floor access and personalized hospitality for premium guests.</p>
        </article>
      </div>
    </div>

    <div class="tab-panel" id="fac-packages">
      <div class="grid-3">
        <article class="feature-card">
          <img src="assets/img/suite_couple.jpg" alt="Couple package suite">
          <h4>Couple Package</h4>
          <p>Romantic suite decor, dinner experience, and late checkout.</p>
        </article>
        <article class="feature-card">
          <img src="assets/img/suite_family.jpg" alt="Family package suite">
          <h4>Family Package</h4>
          <p>Spacious suite options, kids meals, and leisure facility access.</p>
        </article>
        <article class="feature-card">
          <img src="assets/img/facility_lounge.jpg" alt="Celebration package lounge">
          <h4>Celebration Package</h4>
          <p>Occasion setup, lounge event support, and curated food plans.</p>
        </article>
      </div>
    </div>
  </section>
</main>
<?php require dirname(__DIR__) . '/app/views/partials/footer.php'; ?>
