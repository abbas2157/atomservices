<?php
$pageTitle = 'Terms of Use | Atom Services';
$metaDescription = 'The terms for using atomservices.org, the company website of Atom Services (Pvt) Ltd, Lahore: content, intellectual property, acceptable use and liability.';
$canonicalPath = 'terms.php';

require_once __DIR__ . '/includes/site.php';

$sections = [
  'accept' => 'Using this site',
  'info' => 'Information on the site',
  'ip' => 'Intellectual property',
  'conduct' => 'Acceptable use',
  'links' => 'Links to other sites',
  'liability' => 'Liability',
  'law' => 'Governing law',
  'changes' => 'Changes and contact',
];

include __DIR__ . '/includes/header.php';
?>

  <section class="page-hero band">
    <div class="wrap">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="./">Home</a> <span aria-hidden="true">/</span> <span aria-current="page">Terms of use</span></nav>
      <span class="eyebrow">Legal</span>
      <h1>Terms of use.</h1>
      <p class="hero-lede">The rules for using atomservices.org.</p>
      <span class="updated">Effective 26 October 2026</span>
    </div>
  </section>

  <section class="section band">
    <div class="wrap doc">
      <nav class="toc" aria-label="Contents">
        <b>Contents</b>
<?php $n = 0; foreach ($sections as $id => $title): ?>
        <a href="#<?php echo $id; ?>"><?php echo ++$n . '. ' . e($title); ?></a>
<?php endforeach; ?>
      </nav>
      <article class="prose">
        <h2 id="accept">1. Using this site</h2>
        <p>atomservices.org is operated by <strong>Atom Services (Pvt) Ltd</strong>. By using the site you agree to these terms. If you do not agree, please do not use it.</p>

        <h2 id="info">2. Information on the site</h2>
        <p>Content on this site describes our company and products for general information. Figures such as customer counts and amounts financed are stated as of the date shown and may change. Product terms, prices and eligibility are set on each product's own website and app, and those terms apply when you use the product.</p>
        <p>Nothing on this site is an offer of credit, investment advice or a solicitation to invest.</p>

        <h2 id="ip">3. Intellectual property</h2>
        <p>The Atom Services name and logo, and the names and logos of Atomshop, AtomPay, Atom Seller, AtomBrand, Mark Attendance, KDP Score and Publishing Guru, together with the site's design and content, belong to Atom Services or its licensors. You may view and share pages for personal or business reference, but you may not copy, modify or reuse them commercially without our written permission. Third-party brand names belong to their owners.</p>

        <h2 id="conduct">4. Acceptable use</h2>
        <ul>
          <li>Do not attempt to break, overload or gain unauthorised access to the site.</li>
          <li>Do not submit false information or impersonate someone else in our forms.</li>
          <li>Do not scrape the site or use it to send spam.</li>
        </ul>

        <h2 id="links">5. Links to other sites</h2>
        <p>The site links to our product websites and to third parties such as WhatsApp and social networks. We are not responsible for third-party sites or their content.</p>

        <h2 id="liability">6. Liability</h2>
        <p>The site is provided "as is". To the extent the law allows, we are not liable for indirect or consequential loss arising from use of the site. Nothing in these terms limits liability that cannot be limited by law.</p>

        <h2 id="law">7. Governing law</h2>
        <p>These terms are governed by the laws of Pakistan, and the courts at Lahore have jurisdiction, unless the law of your country requires otherwise.</p>

        <h2 id="changes">8. Changes and contact</h2>
        <p>We may update these terms; the effective date shows the latest version. Questions: <a href="mailto:<?php echo CONTACT_EMAIL; ?>"><?php echo CONTACT_EMAIL; ?></a>. See also our <a href="privacy-policy.php">privacy policy</a>.</p>
      </article>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
