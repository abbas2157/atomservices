<?php
$pageTitle = 'Contact Atom Services: Lahore, Pakistan and Wyoming, USA';
$metaDescription = 'Contact Atom Services (Pvt) Ltd in Lahore and Atom Services LLC in the US. Email info@atomservices.org, message us on WhatsApp or use the form.';
$canonicalPath = 'contact';

require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/includes/form_parts.php';

include __DIR__ . '/includes/header.php';
?>

  <section class="page-hero band">
    <div class="wrap">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="./">Home</a> <span aria-hidden="true">/</span> <span aria-current="page">Contact us</span></nav>
      <span class="eyebrow">Contact</span>
      <h1>Talk to the right team.</h1>
      <p class="hero-lede">Investors, partners and customers can reach us by email, phone or the form on this page. We reply within one working day.</p>
    </div>
  </section>

  <section class="section band">
    <div class="wrap contact-grid">
      <div class="contact-side">
        <h2 class="sr-only">Ways to reach us</h2>
        <div class="contact-cards">
          <div class="ccard wide">
            <span class="tier">Head office</span>
            <h3><?php echo e(SITE_NAME); ?></h3>
            <p><?php echo e(COMPANY_OFFICE); ?></p>
            <p><a href="mailto:<?php echo CONTACT_EMAIL; ?>"><b><?php echo CONTACT_EMAIL; ?></b></a> <button class="copy-btn light" type="button" data-copy="<?php echo CONTACT_EMAIL; ?>" aria-label="Copy email address">Copy</button></p>
            <p><a href="tel:+<?php echo WHATSAPP_NUMBER; ?>"><?php echo CONTACT_PHONE; ?></a><?php if (COMPANY_PK_PHONE): ?> · <?php echo e(COMPANY_PK_PHONE); ?><?php endif; ?> · <a href="<?php echo e(whatsapp_url()); ?>" target="_blank" rel="noopener">WhatsApp</a></p>
          </div>
          <div class="ccard">
            <?php echo brand_img('atomshop', 'Atomshop.pk logo', 'ccard-logo'); ?>
            <span class="tier">Shoppers and sellers</span>
            <h3>Atomshop.pk</h3>
            <p><a href="https://wa.me/923302277522" target="_blank" rel="noopener">+92 330 2277522</a> (WhatsApp)</p>
            <p><a href="mailto:atomshoppk@gmail.com">atomshoppk@gmail.com</a></p>
            <a href="<?php echo e(product_url('https://atomshop.pk/contact-us', 'contact')); ?>" target="_blank" rel="noopener">atomshop.pk/contact-us</a>
          </div>
          <div class="ccard">
            <?php echo brand_img('mark-attendance', 'Mark Attendance logo', 'ccard-logo tall'); ?>
            <span class="tier">Attendance demos</span>
            <h3>Mark Attendance</h3>
            <p><a href="https://wa.me/923064164077" target="_blank" rel="noopener">+92 306 4164077</a> (WhatsApp)</p>
            <p><a href="mailto:info@markattendance.io">info@markattendance.io</a></p>
            <a href="<?php echo e(product_url('https://markattendance.io/contact', 'contact')); ?>" target="_blank" rel="noopener">markattendance.io/contact</a>
          </div>
          <div class="ccard">
            <?php echo brand_img('publishing-guru', 'Publishing Guru logo', 'ccard-logo'); ?>
            <span class="tier">KDP publishing</span>
            <h3>Publishing Guru</h3>
            <p><a href="https://wa.me/17208034953" target="_blank" rel="noopener">+1 720 803 4953</a> (WhatsApp)</p>
            <a href="<?php echo e(product_url('https://publishinguru.com/contact', 'contact')); ?>" target="_blank" rel="noopener">publishinguru.com/contact</a>
          </div>
          <div class="ccard">
            <span class="tier">United States</span>
            <h3>Atom Services LLC</h3>
            <p>Sheridan, Wyoming, USA</p>
            <p><a href="tel:+<?php echo WHATSAPP_NUMBER; ?>"><?php echo CONTACT_PHONE; ?></a></p>
          </div>
        </div>
        <p class="hours">Office hours: Monday to Saturday, 10:00–19:00 Pakistan time (UTC+5)</p>
      </div>

      <div class="form-panel" id="message">
        <form class="meet contact-form" action="forms/contact.php" method="post" novalidate data-ajax>
          <h3>Send us a message</h3>
          <input type="hidden" name="form" value="enquiry">
          <?php echo form_honeypot('e'); ?>
          <div class="two">
            <div class="field"><label for="e-name">Name <abbr title="required">*</abbr></label><input id="e-name" name="name" required maxlength="150" autocomplete="name"></div>
            <div class="field"><label for="e-company">Company</label><input id="e-company" name="company" maxlength="150" autocomplete="organization"></div>
          </div>
          <div class="field"><label for="e-email">Email <abbr title="required">*</abbr></label><input id="e-email" name="email" type="email" required maxlength="190" autocomplete="email" inputmode="email"></div>
          <div class="field"><label for="e-topic">Topic</label>
            <select id="e-topic" name="topic"><?php foreach (CONTACT_ENQUIRY_TOPICS as $topic): ?><option><?php echo e($topic); ?></option><?php endforeach; ?></select></div>
          <div class="field"><label for="e-message">Message <abbr title="required">*</abbr></label><textarea id="e-message" name="message" required minlength="10" maxlength="5000" placeholder="How can we help?"></textarea></div>
          <button class="btn btn-amber" type="submit"><span class="spinner" aria-hidden="true"></span>Send message</button>
          <?php echo form_status('enquiry'); ?>
          <p class="form-alt">By sending this form you agree to our <a href="privacy-policy">privacy policy</a>.</p>
        </form>
        <?php echo form_success('Message sent', 'Send another message'); ?>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/cta_band.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
