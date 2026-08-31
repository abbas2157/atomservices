<?php
$pageTitle = 'Portfolio Details - Atom Services';
$metaDescription = 'A closer look at the software and app platforms built by Atom Services (Pvt) Ltd.';
$bodyClass = 'portfolio-details-page';
$needsSwiper = true;
include __DIR__ . '/includes/header.php';
?>

    <!-- Page Title -->
    <div class="page-title">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Portfolio Details</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php">Home</a></li>
            <li class="current">Portfolio Details</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Portfolio Details Section -->
    <section id="portfolio-details" class="portfolio-details section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          <div class="col-lg-8">
            <div class="portfolio-details-slider swiper init-swiper">

              <script type="application/json" class="swiper-config">
                {
                  "loop": true,
                  "speed": 600,
                  "autoplay": {
                    "delay": 5000
                  },
                  "slidesPerView": "auto",
                  "pagination": {
                    "el": ".swiper-pagination",
                    "type": "bullets",
                    "clickable": true
                  }
                }
              </script>

              <div class="swiper-wrapper align-items-center">

                <div class="swiper-slide">
                  <picture>
                    <source srcset="assets/img/portfolio/atomshop.webp" type="image/webp">
                    <img src="assets/img/portfolio/atomshop.png" alt="Atomshop.pk" loading="lazy" decoding="async">
                  </picture>
                </div>

                <div class="swiper-slide">
                  <picture>
                    <source srcset="assets/img/portfolio/SOPS.webp" type="image/webp">
                    <img src="assets/img/portfolio/SOPS.png" alt="School of Professional Skills" loading="lazy" decoding="async">
                  </picture>
                </div>

                <div class="swiper-slide">
                  <picture>
                    <source srcset="assets/img/portfolio/Daily-english.webp" type="image/webp">
                    <img src="assets/img/portfolio/Daily-english.png" alt="Daily English" loading="lazy" decoding="async">
                  </picture>
                </div>

              </div>
              <div class="swiper-pagination"></div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="portfolio-info" data-aos="fade-up" data-aos-delay="200">
              <h3>Project information</h3>
              <ul>
                <li><strong>Category</strong>: SaaS / Web platform</li>
                <li><strong>Client</strong>: Atomshop.pk</li>
                <li><strong>Project date</strong>: 2024</li>
                <li><strong>Project URL</strong>: <a href="https://atomshop.pk" target="_blank" rel="noopener">atomshop.pk</a></li>
              </ul>
            </div>
            <div class="portfolio-description" data-aos="fade-up" data-aos-delay="300">
              <h2>Pakistan's leading installment shopping platform</h2>
              <p>
                Atomshop.pk lets customers shop online and pay in easy installments. Atom Services designed, built and continues to support the platform as a scalable SaaS product used by digital entrepreneurs across Pakistan.
              </p>
            </div>
          </div>

        </div>

      </div>

    </section><!-- /Portfolio Details Section -->

<?php include __DIR__ . '/includes/footer.php'; ?>
