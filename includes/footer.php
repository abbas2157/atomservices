  </main>

  <footer id="footer" class="footer">

    <div class="footer-newsletter">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-lg-6">
            <h4>Join Our Newsletter</h4>
            <p>Subscribe to our newsletter and receive the latest news about our products and services!</p>
            <form action="forms/newsletter.php" method="post" class="php-email-form">
              <div class="newsletter-form"><input type="email" name="email" aria-label="Email address" required><input type="submit" value="Subscribe"></div>
              <div class="loading">Loading</div>
              <div class="error-message"></div>
              <div class="sent-message">Your subscription request has been sent. Thank you!</div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.php" class="d-flex align-items-center">
            <span class="sitename">Atom Services</span>
          </a>
          <div class="footer-contact pt-3">
            <p>Sheridan, Wyoming</p>
            <p class="mt-3"><strong>Phone:</strong> <span>+16467260635</span></p>
            <p><strong>Email:</strong> <span>info@atomservices.org</span></p>
          </div>
        </div>

        <div class="col-lg-4 col-md-3 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><i class="bi bi-chevron-right"></i> <a href="<?php echo $home; ?>#hero">Home</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="<?php echo $home; ?>#about">About us</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="<?php echo $home; ?>#services">Services</a></li>
          </ul>
        </div>

        <div class="col-lg-4 col-md-12">
          <h4>Follow Us</h4>
          <p>Atom Services LLC is a group of renowned IT Professional Agencies. It serves as a One-Stop-Shop for all their Customers, and Organizations. We help Building Businesses from Scratch.</p>
          <div class="social-links d-flex">
            <a href="https://www.facebook.com/AtomServicesOfficial/" target="_blank" rel="noopener" aria-label="Atom Services on Facebook"><i class="bi bi-facebook"></i></a>
            <a href="https://www.linkedin.com/company/atomservicesofficial/" target="_blank" rel="noopener" aria-label="Atom Services on LinkedIn"><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">atomservices.org</strong> <span>All Rights Reserved <?php echo date('Y'); ?></span></p>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center" aria-label="Scroll to top"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/php-email-form/validate.js" defer></script>
  <script src="assets/vendor/aos/aos.js" defer></script>
<?php if ($needsGlightbox): ?>
  <script src="assets/vendor/glightbox/js/glightbox.min.js" defer></script>
<?php endif; ?>
<?php if ($needsPureCounter): ?>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js" defer></script>
<?php endif; ?>
<?php if ($needsIsotope): ?>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js" defer></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js" defer></script>
<?php endif; ?>
<?php if ($needsSwiper): ?>
  <script src="assets/vendor/swiper/swiper-bundle.min.js" defer></script>
<?php endif; ?>

  <!-- Main JS File -->
  <script src="assets/js/main.js" defer></script>

</body>

</html>
