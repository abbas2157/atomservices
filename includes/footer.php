</main>

<footer class="band" id="site-footer">
  <div class="wrap">
    <div class="footer-about">
      <a class="brand" href="<?php echo $home === '' ? '#top' : $home; ?>"><?php echo logo_mark(); ?> Atom Services</a>
      <p>AI-powered product financing and software, built in Lahore. Smarter finance, better tomorrow.</p>
      <div class="foot-legal">
        <span>Atom Services (Pvt) Ltd</span>
<?php if (COMPANY_SECP_NO): ?>
        <span>SECP Reg. No. <?php echo e(COMPANY_SECP_NO); ?></span>
<?php endif; ?>
<?php if (COMPANY_NTN): ?>
        <span>NTN <?php echo e(COMPANY_NTN); ?></span>
<?php endif; ?>
        <span><?php echo e(COMPANY_US_ENTITY); ?></span>
      </div>
    </div>
    <div>
      <h2 class="foot-title">Contact</h2>
      <ul>
        <li><a href="mailto:<?php echo CONTACT_EMAIL; ?>"><?php echo CONTACT_EMAIL; ?></a> <button class="copy-btn" type="button" data-copy="<?php echo CONTACT_EMAIL; ?>" aria-label="Copy email address">Copy</button></li>
        <li><a href="tel:+<?php echo WHATSAPP_NUMBER; ?>"><?php echo CONTACT_PHONE; ?></a></li>
        <li><a href="<?php echo e(whatsapp_url()); ?>" target="_blank" rel="noopener">Chat on WhatsApp</a></li>
        <li>Lahore, Pakistan</li>
        <li>Sheridan, Wyoming, USA</li>
      </ul>
    </div>
    <div>
      <h2 class="foot-title">Products</h2>
      <ul>
        <li><a href="<?php echo e(product_url('https://atomshop.pk', 'footer')); ?>" target="_blank" rel="noopener">Atomshop.pk</a></li>
        <li><a href="<?php echo e(product_url('https://atompay.shop', 'footer')); ?>" target="_blank" rel="noopener">AtomPay</a></li>
        <li><a href="<?php echo e(product_url('https://markattendance.io', 'footer')); ?>" target="_blank" rel="noopener">Mark Attendance</a></li>
        <li><a href="<?php echo e(product_url('https://kdpscore.com', 'footer')); ?>" target="_blank" rel="noopener">KDP Score</a></li>
        <li><a href="<?php echo e(product_url('https://publishinguru.com', 'footer')); ?>" target="_blank" rel="noopener">Publishing Guru</a></li>
      </ul>
    </div>
    <div>
      <h2 class="foot-title">Company</h2>
      <ul>
        <li><a href="about.php">About us</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="contact.php">Contact us</a></li>
        <li><a href="faqs.php">FAQs</a></li>
        <li><a href="privacy-policy.php">Privacy policy</a></li>
        <li><a href="terms.php">Terms of use</a></li>
      </ul>
    </div>
    <div class="legal"><span>© <?php echo date('Y'); ?> Atom Services (Pvt) Ltd. All rights reserved.</span><span><a href="https://www.linkedin.com/company/atomservicesofficial/" target="_blank" rel="noopener">LinkedIn</a> · <a href="https://www.facebook.com/AtomServicesOfficial/" target="_blank" rel="noopener">Facebook</a></span></div>
  </div>
</footer>

<a class="wa-float" href="<?php echo e(whatsapp_url()); ?>" target="_blank" rel="noopener" aria-label="Chat with Atom Services on WhatsApp">
  <svg viewBox="0 0 32 32" aria-hidden="true"><path fill="currentColor" d="M16.04 3C8.86 3 3.03 8.82 3.03 16c0 2.3.6 4.53 1.74 6.5L3 29l6.68-1.75A12.95 12.95 0 0 0 16.04 29C23.2 29 29 23.18 29 16S23.2 3 16.04 3Zm0 23.8c-2 0-3.95-.54-5.65-1.55l-.4-.24-3.97 1.04 1.06-3.86-.26-.4A10.76 10.76 0 0 1 5.2 16c0-5.97 4.86-10.82 10.84-10.82 5.97 0 10.8 4.85 10.8 10.82 0 5.97-4.83 10.8-10.8 10.8Zm5.93-8.1c-.33-.16-1.93-.95-2.23-1.06-.3-.11-.52-.16-.73.17-.22.32-.84 1.05-1.03 1.27-.19.22-.38.24-.7.08-.33-.16-1.38-.51-2.62-1.62-.97-.86-1.62-1.93-1.81-2.25-.19-.33-.02-.5.14-.66.15-.15.33-.38.49-.57.16-.19.22-.33.33-.54.11-.22.05-.41-.03-.57-.08-.16-.73-1.76-1-2.41-.26-.63-.53-.55-.73-.56h-.62c-.22 0-.57.08-.87.41-.3.33-1.14 1.11-1.14 2.71s1.17 3.15 1.33 3.36c.16.22 2.3 3.5 5.56 4.91.78.34 1.39.54 1.86.69.78.25 1.49.21 2.05.13.63-.09 1.93-.79 2.2-1.55.27-.76.27-1.42.19-1.55-.08-.14-.3-.22-.62-.38Z"/></svg>
  <span>WhatsApp</span>
</a>
<button class="to-top" id="to-top" type="button" aria-label="Back to top"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5.5 11.5 12 5l6.5 6.5"/></svg></button>

<div class="cookie-banner" id="cookie-banner" role="region" aria-label="Cookie notice" hidden>
  <p>We only use essential browser storage. No advertising or tracking cookies.</p>
  <button class="btn btn-amber" type="button">OK, got it</button>
</div>

<script src="assets/js/lib.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/lib.js'); ?>" defer></script>
<script src="assets/js/main.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/main.js'); ?>" defer></script>
</body>
</html>
