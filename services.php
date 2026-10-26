<?php
$pageTitle = 'Software Development Services: Custom Software, Mobile Apps, SaaS, UI/UX | Atom Services';
$metaDescription = 'Custom software, Android and iOS apps, SaaS platforms and UI/UX design from the Lahore team that builds and runs Atomshop.pk and Mark Attendance. Tell us about your project.';
$canonicalPath = 'services';

require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/includes/form_parts.php';

$services = [
  [
    'id' => 'custom-software',
    'name' => 'Custom software',
    'tagline' => 'Business systems built to scale.',
    'body' => 'Internal tools, customer portals, dashboards and back-office systems shaped around how your team actually works, not around a template.',
    'items' => ['Web applications and portals', 'APIs and third-party integrations', 'Admin dashboards and reporting', 'Payments, KYC and financing workflows'],
    'proof' => 'We built the shared data layer and admin behind all four Atomshop apps.',
  ],
  [
    'id' => 'mobile-apps',
    'name' => 'Mobile apps',
    'tagline' => 'Android and iOS apps built for performance.',
    'body' => 'Customer and field-team apps that stay fast on mid-range phones and patchy connections, and publish cleanly to Google Play and the App Store.',
    'items' => ['Android and iOS apps', 'Offline-first data sync', 'Push notifications and reminders', 'Store listing and release'],
    'proof' => 'Atomshop, AtomPay, Atom Seller and AtomBrand run on one shared account across devices.',
  ],
  [
    'id' => 'saas',
    'name' => 'SaaS platforms',
    'tagline' => 'Cloud products, launch-ready.',
    'body' => 'From first version to paying customers: accounts, subscriptions, roles and the operational plumbing a SaaS product needs on day one.',
    'items' => ['Multi-tenant architecture', 'Subscriptions and billing', 'Roles and permissions', 'Hosting, monitoring and backups'],
    'proof' => 'Mark Attendance and KDP Score are SaaS products we designed, built and operate ourselves.',
  ],
  [
    'id' => 'ui-ux',
    'name' => 'UI/UX design',
    'tagline' => 'User-centred digital experiences.',
    'body' => 'Research, flows and interface design that make complex things, like an installment plan or a credit limit, feel simple to the person using them.',
    'items' => ['User research and flows', 'Wireframes and prototypes', 'Interface and design systems', 'Usability review of existing products'],
    'proof' => 'Atomshop turns a financing decision into a plan a shopper understands in seconds.',
  ],
];

include __DIR__ . '/includes/header.php';
?>

  <section class="page-hero band">
    <div class="wrap">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="./">Home</a> <span aria-hidden="true">/</span> <span aria-current="page">Services</span></nav>
      <span class="eyebrow">Services</span>
      <h1>Software development services from the team behind Atomshop.</h1>
      <p class="hero-lede">Custom software, mobile apps, SaaS platforms and UI/UX design, built by the Lahore team that runs its own products every day.</p>
      <div class="hero-cta">
        <a class="btn btn-amber" href="#contact">Tell us about your project</a>
        <a class="btn btn-ghost" href="<?php echo e(whatsapp_url('Hi Atom Services, I have a software project I would like to discuss.')); ?>" target="_blank" rel="noopener">Chat on WhatsApp</a>
      </div>
    </div>
  </section>

  <section class="traction band" aria-label="Products we run">
    <div class="wrap">
      <div class="stat"><b>5</b><span>products we own and run</span></div>
      <div class="stat"><b>4,000+</b><span>Atomshop customers</span></div>
      <div class="stat"><b>100+</b><span>books launched by Publishing Guru</span></div>
      <div class="stat"><b>PK · US</b><span>registered in Pakistan and the USA</span></div>
    </div>
  </section>

  <section class="section band">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">What we build</span>
        <h2>Four services, one team that ships its own products.</h2>
        <p>Every service below is something we use on our own platforms first. Jump to one: <a href="#custom-software">custom software</a>, <a href="#mobile-apps">mobile apps</a>, <a href="#saas">SaaS</a>, <a href="#ui-ux">UI/UX</a>.</p>
      </div>
      <div class="svc-list">
<?php foreach ($services as $i => $s): ?>
        <article class="svc-item" id="<?php echo $s['id']; ?>">
          <span class="svc-num" aria-hidden="true"><?php echo str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); ?></span>
          <div class="svc-main">
            <h2><?php echo e($s['name']); ?></h2>
            <p class="tagline"><?php echo e($s['tagline']); ?></p>
            <p><?php echo e($s['body']); ?></p>
            <p class="svc-proof"><b>In our own products:</b> <?php echo e($s['proof']); ?></p>
          </div>
          <ul class="svc-points">
<?php foreach ($s['items'] as $item): ?>
            <li><?php echo e($item); ?></li>
<?php endforeach; ?>
          </ul>
        </article>
<?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section band products-section">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">How we work</span>
        <h2>From first call to launch, and after.</h2>
      </div>
      <ol class="process">
        <li><b>Discover</b><span>A short call to understand the problem, the people using it and what success looks like.</span></li>
        <li><b>Design</b><span>Flows and screens you can click through before any code is written.</span></li>
        <li><b>Build</b><span>Working software in small releases, so you see progress every step of the way.</span></li>
        <li><b>Launch and support</b><span>Release to your customers, then monitoring and improvements once it is live.</span></li>
      </ol>
      <p class="see-also">See what we run ourselves: <a href="./#products">our products</a> and <a href="./#ecosystem">the Atomshop ecosystem</a>. Meeting us in Dubai? <a href="./#gitex">Book a slot at GITEX 2026</a>.</p>
    </div>
  </section>

  <section class="section band gitex" id="contact">
    <div class="wrap">
      <div class="gitex-grid">
        <div class="section-head">
          <span class="eyebrow">Start a project</span>
          <h2>Tell us what you want to build.</h2>
          <p>Send a few lines about your project and we'll reply by email. Prefer to talk? Email <a href="mailto:<?php echo CONTACT_EMAIL; ?>"><?php echo CONTACT_EMAIL; ?></a> or <a href="<?php echo e(whatsapp_url()); ?>" target="_blank" rel="noopener">message us on WhatsApp</a>.</p>
        </div>
        <div class="form-panel">
          <form class="meet" action="forms/contact.php" method="post" novalidate data-ajax>
            <h3>Project enquiry</h3>
            <input type="hidden" name="form" value="contact">
            <?php echo form_honeypot('c'); ?>
            <div class="two">
              <div class="field"><label for="c-name">Name <abbr title="required">*</abbr></label><input id="c-name" name="name" required maxlength="150" autocomplete="name"></div>
              <div class="field"><label for="c-company">Company</label><input id="c-company" name="company" maxlength="150" autocomplete="organization"></div>
            </div>
            <div class="two">
              <div class="field"><label for="c-email">Email <abbr title="required">*</abbr></label><input id="c-email" name="email" type="email" required maxlength="190" autocomplete="email" inputmode="email"></div>
              <div class="field"><label for="c-service">Service</label>
                <select id="c-service" name="service"><?php foreach (CONTACT_SERVICES as $service): ?><option><?php echo e($service); ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="field"><label for="c-message">About your project <abbr title="required">*</abbr></label><textarea id="c-message" name="message" required minlength="10" maxlength="5000" placeholder="What are you building, and who is it for?"></textarea></div>
            <button class="btn btn-amber" type="submit"><span class="spinner" aria-hidden="true"></span>Send enquiry</button>
            <?php echo form_status('contact'); ?>
            <p class="form-alt">By sending this form you agree to our <a href="privacy-policy">privacy policy</a>.</p>
          </form>
          <?php echo form_success('Message sent', 'Send another message'); ?>
        </div>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
