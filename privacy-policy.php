<?php
$pageTitle = 'Privacy Policy | Atom Services';
$metaDescription = 'How Atom Services (Pvt) Ltd collects, uses and protects personal data on atomservices.org.';
$canonicalPath = 'privacy-policy.php';

require_once __DIR__ . '/includes/site.php';

$sections = [
  'who' => 'Who we are',
  'collect' => 'Data we collect',
  'use' => 'How we use it',
  'share' => 'Sharing',
  'transfer' => 'International transfers',
  'keep' => 'Retention and security',
  'cookies' => 'Cookies and browser storage',
  'rights' => 'Your choices and rights',
  'children' => 'Children',
  'changes' => 'Changes',
  'privacy-contact' => 'Contact',
];

include __DIR__ . '/includes/header.php';
?>

  <section class="page-hero band">
    <div class="wrap">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="./">Home</a> <span aria-hidden="true">/</span> <span aria-current="page">Privacy policy</span></nav>
      <span class="eyebrow">Legal</span>
      <h1>Privacy policy.</h1>
      <p class="hero-lede">How we collect, use and protect personal data on atomservices.org.</p>
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
        <h2 id="who">1. Who we are</h2>
        <p>This policy explains how <strong>Atom Services (Pvt) Ltd</strong> and its US affiliate <strong>Atom Services LLC</strong> ("Atom Services", "we", "us") handle personal data collected through atomservices.org.</p>
        <p>Each of our products, including Atomshop.pk, AtomPay, Mark Attendance, KDP Score and Publishing Guru, has its own privacy policy for data collected inside that product. Where a product policy applies, it takes precedence for that product.</p>

        <h2 id="collect">2. Data we collect</h2>
        <ul>
          <li><strong>Information you give us:</strong> your name, company, email and message, and for meeting requests your preferred day and topic, when you use a form on this site. If you email us, message us on WhatsApp or meet us at an event, we also receive what you share there, such as your phone number.</li>
          <li><strong>Technical data:</strong> the IP address recorded with each form submission, and the IP address, browser type, pages requested and referring site recorded in our web server logs.</li>
          <li><strong>Campaign information:</strong> if you arrive through a link tagged with campaign parameters (for example <code>utm_source=linkedin</code>), those tags are sent with any form you submit, so we know which campaign reached you.</li>
        </ul>
        <p>This website does not ask for CNIC numbers, financial details or biometric data.</p>

        <h2 id="use">3. How we use it</h2>
        <ul>
          <li>To reply to enquiries and arrange meetings, including at GITEX Global 2026.</li>
          <li>To understand which of our campaigns and channels bring people to us.</li>
          <li>To run, secure and improve the website, including filtering spam.</li>
          <li>To meet legal, tax and regulatory obligations.</li>
        </ul>
        <p>We rely on your consent, our legitimate interest in running and promoting our business, and our legal obligations.</p>

        <h2 id="share">4. Sharing</h2>
        <p>We do not sell personal data. We share it only with:</p>
        <ul>
          <li>Service providers who host the site and deliver our email, under terms that limit their use of the data.</li>
          <li>Companies within the Atom Services group, where your enquiry concerns one of our products.</li>
          <li>Authorities, where the law requires it.</li>
          <li>A buyer or successor, if our business or part of it is sold or merged.</li>
        </ul>
        <p>If you choose to contact us on WhatsApp, that conversation is also subject to WhatsApp's own terms and privacy policy.</p>

        <h2 id="transfer">5. International transfers</h2>
        <p>We operate in Pakistan and the United States, and our providers may store data in other countries. When data moves between countries, we take reasonable steps to keep it protected to the standard described in this policy.</p>

        <h2 id="keep">6. Retention and security</h2>
        <p>We keep enquiry and meeting data for up to 24 months after our last contact, unless a longer period is needed for a contract or the law. We use encryption in transit, access controls and regular reviews to protect data. No system is completely secure, so please do not send sensitive information through website forms.</p>

        <h2 id="cookies">7. Cookies and browser storage</h2>
        <p>This site does not use advertising or analytics cookies, and it loads no third-party tracking scripts. It keeps two small items in your browser:</p>
        <ul>
          <li>a note that you have dismissed the cookie notice, kept until you clear your browser data; and</li>
          <li>the campaign tags described in section 2, kept only until you close the browser tab.</li>
        </ul>
        <p>You can clear both in your browser settings at any time; the site will still work. If we add analytics in future, we will update this policy first.</p>

        <h2 id="rights">8. Your choices and rights</h2>
        <p>You can ask us to give you a copy of your data, correct it, delete it, or stop using it for marketing. Email <a href="mailto:<?php echo CONTACT_EMAIL; ?>?subject=Privacy%20request"><?php echo CONTACT_EMAIL; ?></a> with "Privacy request" in the subject. We may need to confirm your identity and will reply within 30 days.</p>

        <h2 id="children">9. Children</h2>
        <p>This website is not directed at anyone under 18, and we do not knowingly collect their data. If you believe a child has sent us personal data, contact us and we will delete it.</p>

        <h2 id="changes">10. Changes</h2>
        <p>We may update this policy. The date at the top of the page shows when it last changed, and significant changes will be highlighted on the site.</p>

        <h2 id="privacy-contact">11. Contact</h2>
        <p><?php echo e(SITE_NAME); ?><br><?php echo e(COMPANY_OFFICE); ?><br>Email: <a href="mailto:<?php echo CONTACT_EMAIL; ?>"><?php echo CONTACT_EMAIL; ?></a><br>Phone: <?php echo CONTACT_PHONE; ?></p>
      </article>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
