<?php
$pageTitle = 'Service Details - Atom Services';
$metaDescription = 'Details of the custom software, mobile app and SaaS product development services offered by Atom Services (Pvt) Ltd.';
$bodyClass = 'service-details-page';
include __DIR__ . '/includes/header.php';
?>

    <!-- Page Title -->
    <div class="page-title">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Service Details</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php">Home</a></li>
            <li class="current">Service Details</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Service Details Section -->
    <section id="service-details" class="service-details section">

      <div class="container">

        <div class="row gy-5">

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">

            <div class="service-box">
              <h4>Services List</h4>
              <div class="services-list">
                <a href="#" class="active"><i class="bi bi-arrow-right-circle"></i><span>Custom Software</span></a>
                <a href="#"><i class="bi bi-arrow-right-circle"></i><span>App Development</span></a>
                <a href="#"><i class="bi bi-arrow-right-circle"></i><span>SaaS Products</span></a>
                <a href="#"><i class="bi bi-arrow-right-circle"></i><span>UI/UX Design</span></a>
              </div>
            </div><!-- End Services List -->

            <div class="help-box d-flex flex-column justify-content-center align-items-center">
              <i class="bi bi-headset help-icon"></i>
              <h4>Have a Question?</h4>
              <p class="d-flex align-items-center mt-2 mb-0"><i class="bi bi-telephone me-2"></i> <span>+16467260635</span></p>
              <p class="d-flex align-items-center mt-1 mb-0"><i class="bi bi-envelope me-2"></i> <a href="mailto:info@atomservices.org">info@atomservices.org</a></p>
            </div>

          </div>

          <div class="col-lg-8 ps-lg-5" data-aos="fade-up" data-aos-delay="200">
            <picture>
              <source srcset="assets/img/about.webp" type="image/webp">
              <img src="assets/img/about.png" alt="Atom Services software development" class="img-fluid services-img" width="800" height="565" loading="lazy" decoding="async">
            </picture>
            <h3>Custom software, built around how your business actually runs</h3>
            <p>
              We design and build business-first software, mobile apps and SaaS platforms that scale with you. From a fast MVP to a full production platform, our team stays involved for the long haul with reliable, ongoing support.
            </p>
            <ul>
              <li><i class="bi bi-check-circle"></i> <span>Scalable, business-focused architecture</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Intuitive Android &amp; iOS apps built for performance</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Launch-ready, cloud-based SaaS platforms</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Sleek, user-centered UI/UX design</span></li>
            </ul>
            <p>
              We have a proven track record with our own in-house platforms, including Atomshop.pk, Pakistan's leading installment shopping system, now available as a SaaS product for digital entrepreneurs.
            </p>
          </div>

        </div>

      </div>

    </section><!-- /Service Details Section -->

<?php include __DIR__ . '/includes/footer.php'; ?>
