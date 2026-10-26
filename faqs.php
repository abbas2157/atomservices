<?php
$pageTitle = 'Atom Services FAQs: Atomshop, AtomPay, Mark Attendance and GITEX 2026';
$metaDescription = 'Answers about Atom Services, Atomshop.pk, AtomPay, Mark Attendance, KDP Score, Publishing Guru and GITEX Global 2026.';
$canonicalPath = 'faqs.php';

require_once __DIR__ . '/includes/site.php';

// Answers are trusted HTML (links allowed). The same list feeds the FAQPage structured data.
$faqs = [
  'company' => ['About the company', [
    ['What does Atom Services do?', 'Atom Services (Pvt) Ltd builds AI-powered product financing and the software around it. Our flagship is Atomshop.pk, an installment shopping ecosystem in Pakistan. We also run Mark Attendance, KDP Score and Publishing Guru.'],
    ['Where are you based?', 'Our team and operations are in Lahore, Pakistan. We also have a US entity, Atom Services LLC, in Sheridan, Wyoming.'],
    ['Do you build software for other companies?', 'Yes. The same team offers custom software, Android and iOS apps, SaaS platforms and UI/UX design. See <a href="services.php">our services</a> or <a href="contact.php">contact us</a> with a short brief.'],
    ['Are you hiring?', 'We hire engineers, designers and growth roles from time to time. Email your CV to <a href="mailto:info@atomservices.org?subject=Careers">info@atomservices.org</a> with "Careers" in the subject.'],
  ]],
  'atomshop' => ['Atomshop.pk and AtomPay', [
    ['How do Atomshop installment plans work?', 'A customer picks a product, or any product from any shop through a custom order, and chooses a plan of 3 to 12 months. Plans start from a 20% advance. A verified seller near the customer delivers and collects the installments.'],
    ['What is AtomPay?', 'AtomPay gives a customer a spending limit after a one-time check of their CNIC and income. The limit can be used on eligible listed products over 3 months with no advance and 0% markup.'],
    ['Which cities do you serve?', 'Atomshop.pk currently delivers in Lahore only. We plan to expand to more cities in Punjab.'],
    ['Does a customer need a credit card or bank account?', 'No. Customers do not need a credit card. Identity is verified with a CNIC.'],
    ['How can a shop or brand join?', 'Local shops apply as Smart Sellers and brands as Brand Partners on <a href="' . e(product_url('https://atomshop.pk/smart-seller', 'faqs')) . '" target="_blank" rel="noopener">atomshop.pk</a>. Each partner runs their business from its own Atom app.'],
  ]],
  'attendance' => ['Mark Attendance', [
    ['What is Mark Attendance?', 'Mark Attendance is a face recognition attendance system for offices, factories and warehouses. Employees check in by looking at a camera, which stops one person signing in for another.'],
    ['What hardware do we need?', 'Any device with a camera. Enrolling an employee takes about two minutes, and most organisations are set up in a day.'],
    ['Does it work without internet?', 'Yes. Attendance is recorded on the device and synced when the connection returns.'],
    ['How do we get a demo?', 'Book a free demo on <a href="' . e(product_url('https://markattendance.io/contact', 'faqs')) . '" target="_blank" rel="noopener">markattendance.io</a> or message <a href="https://wa.me/923064164077" target="_blank" rel="noopener">+92 306 4164077</a> on WhatsApp.'],
  ]],
  'publishing' => ['KDP Score and Publishing Guru', [
    ['How do KDP Score and Publishing Guru fit together?', 'KDP Score uses AI to rate Amazon KDP niches before you write. Publishing Guru then builds, launches and scales the book for you, and helps publishers sell or transfer KDP accounts.'],
    ['What does Publishing Guru charge?', 'Services are priced per task, for example niche research, formatting, covers and Amazon ads management. Current prices are on <a href="' . e(product_url('https://publishinguru.com', 'faqs')) . '" target="_blank" rel="noopener">publishinguru.com</a>.'],
  ]],
  'gitex' => ['GITEX Global 2026', [
    ['Will you be at GITEX Global 2026?', 'Yes. Our leadership team is in Dubai for GITEX Global, 8–11 December 2026, at the Dubai Exhibition Centre in Expo City. The GITEX Scale Summit is on 7 December.'],
    ['How do I book a meeting?', 'Use the <a href="./#meet">meeting form</a> on our homepage, pick a preferred day and we will confirm a time and place.'],
    ['Who should meet you?', 'Investors in embedded finance and BNPL, brands and retailers who want to sell on installments, distribution partners for Mark Attendance in the GCC, and publishers scaling Amazon KDP catalogues.'],
  ]],
  'privacy' => ['Data and privacy', [
    ['How do you handle personal data?', 'We collect only what each service needs and use it for that service. Read our <a href="privacy-policy.php">privacy policy</a> for details.'],
    ['How are CNIC and face data protected?', 'Identity documents and biometric templates are encrypted, access is limited to staff who need it, and they are used only for verification and attendance. Each product\'s own privacy policy explains this in detail.'],
  ]],
];

$faqSchema = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
foreach ($faqs as [, $items]) {
  foreach ($items as [$q, $a]) {
    $faqSchema['mainEntity'][] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => html_entity_decode(strip_tags($a))]];
  }
}

include __DIR__ . '/includes/header.php';
?>
  <script type="application/ld+json"><?php echo json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?></script>

  <section class="page-hero band">
    <div class="wrap">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="./">Home</a> <span aria-hidden="true">/</span> <span aria-current="page">FAQs</span></nav>
      <span class="eyebrow">Help centre</span>
      <h1>Frequently asked questions.</h1>
      <p class="hero-lede">Answers about Atom Services, our products and GITEX Global 2026. Can't find what you need? <a href="contact.php">Contact us</a>.</p>
    </div>
  </section>

  <section class="section band">
    <div class="wrap faq-wrap">
      <nav class="toc" aria-label="FAQ topics">
        <b>Topics</b>
<?php foreach ($faqs as $id => [$title]): ?>
        <a href="#<?php echo $id; ?>"><?php echo e($title); ?></a>
<?php endforeach; ?>
      </nav>
      <div class="faq-list">
<?php foreach ($faqs as $id => [$title, $items]): ?>
        <div class="faq-group" id="<?php echo $id; ?>">
          <h2><?php echo e($title); ?></h2>
<?php foreach ($items as [$q, $a]): ?>
          <details><summary><?php echo e($q); ?></summary><p><?php echo $a; ?></p></details>
<?php endforeach; ?>
        </div>
<?php endforeach; ?>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/cta_band.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
