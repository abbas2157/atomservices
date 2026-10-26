<?php
$pageTitle = 'About Atom Services: AI-powered product financing from Lahore';
$metaDescription = 'Atom Services (Pvt) Ltd is a Lahore-based technology company building Atomshop.pk, AtomPay, Mark Attendance, KDP Score and Publishing Guru.';
$canonicalPath = 'about.php';

require_once __DIR__ . '/includes/site.php';

// Rows with a null value (not yet confirmed) are skipped rather than shown as placeholders.
$details = [
  'Legal name' => SITE_NAME,
  'US entity' => COMPANY_US_ENTITY,
  'SECP registration' => COMPANY_SECP_NO,
  'National Tax Number' => COMPANY_NTN,
  'Office' => COMPANY_OFFICE,
  'Email' => CONTACT_EMAIL,
  'Phone' => CONTACT_PHONE . ' (US)' . (COMPANY_PK_PHONE ? ' · ' . COMPANY_PK_PHONE . ' (PK)' : ''),
  'Brands' => 'Atomshop.pk, AtomPay, Atom Seller, AtomBrand, Mark Attendance, KDP Score, Publishing Guru',
];

include __DIR__ . '/includes/header.php';
?>

  <section class="page-hero band">
    <div class="wrap">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="./">Home</a> <span aria-hidden="true">/</span> <span aria-current="page">About us</span></nav>
      <span class="eyebrow">About Atom Services</span>
      <h1>Smarter finance. Better tomorrow.</h1>
      <p class="hero-lede">We build the technology that lets people in Pakistan buy what they need in monthly steps, and the software that helps businesses run.</p>
    </div>
  </section>

  <section class="section band">
    <div class="wrap split">
      <p class="big-quote">Most families can afford a fridge, a phone or a motorbike <em>in steps</em>. Few can pay for it upfront.</p>
      <div class="prose">
        <p>Atom Services (Pvt) Ltd is a Lahore-based technology company. Our flagship, <strong>Atomshop.pk</strong>, connects shoppers, local sellers, brands and financing on one platform, so a customer can tell us what they want and what they can pay each month and get a plan in seconds.</p>
        <p>We build every product in-house: the consumer apps, the seller and brand tools, and the ATOM Intelligence layer that decides limits, routes orders to verified sellers nearby and reminds customers before each payment is due.</p>
        <p>The same team also runs <strong>Mark Attendance</strong>, a face recognition attendance system for Pakistani workplaces, and two Amazon KDP publishing businesses, <strong>KDP Score</strong> and <strong>Publishing Guru</strong>. We take on client projects too: see <a href="services.php">our services</a>.</p>
      </div>
    </div>
  </section>

  <section class="section band products-section">
    <div class="wrap">
      <div class="section-head"><span class="eyebrow">What we believe</span><h2>How we build.</h2></div>
      <div class="values">
        <div class="value"><h3>Clear numbers</h3><p>Every plan shows the advance, the monthly amount and the total before anyone commits. No hidden fees.</p></div>
        <div class="value"><h3>Local first</h3><p>Orders go to verified sellers in the customer's own area, so delivery and support stay close to home.</p></div>
        <div class="value"><h3>Built in-house</h3><p>We design, build and run our own platforms, from mobile apps to the data layer behind them.</p></div>
        <div class="value"><h3>Data with care</h3><p>Identity and biometric data are used only for what the customer signed up for, and protected accordingly.</p></div>
      </div>
    </div>
  </section>

  <section class="section band">
    <div class="wrap split">
      <div class="section-head flush"><span class="eyebrow">Where we are</span><h2>From one shop to an ecosystem.</h2><p>Atomshop.pk now serves customers across Lahore through four connected apps. <a href="./#ecosystem">See how they fit together</a>.</p></div>
      <div class="timeline">
        <div class="tl"><b>Shoppers</b><h3><?php echo brand_img('atomshop-mark', '', 'tl-logo'); ?>Atomshop</h3><p>Installments on mobiles, laptops, bikes, TVs and appliances, plus custom orders from any shop.</p></div>
        <div class="tl"><b>Financing</b><h3><?php echo brand_img('atompay-mark', '', 'tl-logo'); ?>AtomPay</h3><p>A verified spending limit for listed products: 3 months, no advance, 0% markup.</p></div>
        <div class="tl"><b>Sellers and brands</b><h3><?php echo brand_img('atomseller-mark', '', 'tl-logo'); ?><?php echo brand_img('atombrand-mark', '', 'tl-logo'); ?>Atom Seller and AtomBrand</h3><p>Leads, collections, storefronts and stock management for local sellers and partner brands.</p></div>
        <div class="tl"><b>Next</b><h3>Recovery Radar and KYC Vision</h3><p>Risk prediction, CNIC reading and demand insights, built on the same data layer.</p></div>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/team.php'; ?>

  <section class="section band products-section">
    <div class="wrap">
      <div class="section-head"><span class="eyebrow">Company details</span><h2>Registered information.</h2></div>
      <dl class="details">
<?php foreach ($details as $label => $value): if ($value === null) continue; ?>
        <div><dt><?php echo e($label); ?></dt><dd><?php echo e($value); ?></dd></div>
<?php endforeach; ?>
      </dl>
    </div>
  </section>

<?php include __DIR__ . '/includes/cta_band.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
