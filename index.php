<?php
$pageTitle = 'Atom Services (Pvt) Ltd: AI-powered product financing · GITEX Global 2026';
$metaDescription = 'Atom Services (Pvt) Ltd builds AI-powered product financing and the software around it: Atomshop.pk, AtomPay, Mark Attendance, KDP Score and Publishing Guru. Meet us at GITEX Global 2026 in Dubai.';
$canonicalPath = '';

require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/includes/form_parts.php';

// main.js keeps this current on cached pages.
$gitexDays = days_until_gitex();


include __DIR__ . '/includes/header.php';
?>

  <section class="hero band">
    <svg class="orbits" viewBox="0 0 760 760" aria-hidden="true" fill="none"><g stroke="rgba(238,241,247,.14)" stroke-width="1.2"><ellipse cx="380" cy="380" rx="360" ry="140"/><ellipse cx="380" cy="380" rx="360" ry="140" transform="rotate(60 380 380)"/><ellipse cx="380" cy="380" rx="360" ry="140" transform="rotate(-60 380 380)"/></g><circle cx="740" cy="380" r="5" fill="#F5A524"/><circle cx="200" cy="68" r="4" fill="#F5A524" opacity=".7"/></svg>
    <div class="wrap">
      <div class="hero-copy">
        <a class="gitex-tag" href="#gitex"><span class="dot"></span><b>GITEX Global 2026</b> Dubai Exhibition Centre · 8–11 Dec</a>
        <h1>Shop now. <em>Pay in steps.</em> Built in Lahore.</h1>
        <p class="hero-lede">Atomshop.pk is Pakistan&rsquo;s AI-powered installment ecosystem: shoppers, sellers, brands and financing on one account. Built by Atom Services, the team behind Mark Attendance and Publishing Guru.</p>
        <div class="hero-cta">
          <a class="btn btn-amber" href="#meet">Meet us at GITEX</a>
          <a class="btn btn-ghost" href="#ecosystem">How Atomshop works</a>
        </div>
      </div>

      <div class="plan-wrap">
        <div class="toast t1" aria-hidden="true"><i class="toast-logo"><?php echo brand_img('atompay-mark', '', '', false); ?></i><div><b>AtomPay limit approved</b><span>Example · verified with CNIC</span></div></div>
        <figure class="plan" aria-label="Example Atomshop installment plan">
          <div class="plan-head"><span>Example plan · LED TV</span><strong>Rs. 160,000</strong></div>
          <div>
            <div class="plan-amount">Rs. 26,453<small>/month</small></div>
            <p class="plan-sub">6 months after a 20% advance. No credit card.</p>
          </div>
          <div class="steps" role="img" aria-label="Advance of Rs. 32,000 paid, then six monthly payments of Rs. 26,453, two of them paid">
            <div class="step"><div class="bar adv paid"></div><span>ADV</span></div>
            <div class="step"><div class="bar paid"></div><span>M1</span></div>
            <div class="step"><div class="bar paid"></div><span>M2</span></div>
            <div class="step"><div class="bar"></div><span>M3</span></div>
            <div class="step"><div class="bar"></div><span>M4</span></div>
            <div class="step"><div class="bar"></div><span>M5</span></div>
            <div class="step"><div class="bar"></div><span>M6</span></div>
          </div>
          <div class="plan-foot"><span>Advance <b>Rs. 32,000</b></span><span>Plans <b>3–12 months</b></span></div>
        </figure>
        <div class="toast t2" aria-hidden="true"><i class="toast-logo"><?php echo brand_img('atomseller-mark', '', '', false); ?></i><div><b>Verified seller nearby</b><span>Doorstep delivery in Lahore</span></div></div>
      </div>
    </div>
  </section>

  <section class="traction band" aria-label="Traction">
    <div class="wrap">
      <div class="stat"><b>4,000+</b><span>registered customers</span></div>
      <div class="stat"><b>58+</b><span>verified sellers</span></div>
      <div class="stat"><b>Rs. 4.4 cr+</b><span>financed to date</span></div>
      <div class="stat"><b>4 apps</b><span>on one account</span></div>
      <p class="traction-note">Atomshop.pk figures, Lahore, 2026.</p>
    </div>
  </section>

  <section class="section band" id="ecosystem">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">The Atomshop ecosystem</span>
        <h2>Shoppers, sellers, brands and financing on one platform.</h2>
        <p>An order placed in one app reaches the other three immediately. Limits, routing and reminders are decided in one place.</p>
      </div>
      <div class="eco">
        <article class="app"><div class="app-ic logo-tile" aria-hidden="true"><?php echo brand_img('atomshop-mark', ''); ?></div><span class="app-who">For shoppers</span><h3>Atomshop</h3><p>Buy mobiles, laptops, bikes and appliances on installments, or custom-order anything from any shop, and track every payment.</p><?php echo app_links('atomshop', 'Atomshop'); ?></article>
        <article class="app"><div class="app-ic logo-tile" aria-hidden="true"><?php echo brand_img('atompay-mark', ''); ?></div><span class="app-who">For financing</span><h3>AtomPay</h3><p>Verify once with CNIC and income to get a smart limit for eligible products: 3 months, no advance, 0% markup.</p><?php echo app_links('atompay', 'AtomPay'); ?></article>
        <article class="app"><div class="app-ic logo-tile" aria-hidden="true"><?php echo brand_img('atomseller-mark', ''); ?></div><span class="app-who">For local sellers</span><h3>Atom Seller</h3><p>Leads from the seller's own area, deals, guarantors and collections, all from a phone.</p><?php echo app_links('atomseller', 'Atom Seller'); ?></article>
        <article class="app"><div class="app-ic logo-tile" aria-hidden="true"><?php echo brand_img('atombrand-mark', ''); ?></div><span class="app-who">For brands</span><h3>AtomBrand</h3><p>Brands run their storefront, stock, orders and bulk quotes, and sell across Pakistan without extra ad spend.</p><?php echo app_links('atombrand', 'AtomBrand'); ?></article>
        <div class="core">
          <div class="core-copy"><h3>ATOM Intelligence</h3><p>One login and one data layer behind every app.</p></div>
          <div class="chips"><span class="chip">Smart Limit</span><span class="chip">Real-time routing</span><span class="chip">Timed reminders</span><span class="chip">Recovery Radar · next</span><span class="chip">KYC Vision · next</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section band products-section" id="products">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">Platforms we own</span>
        <h2>One flagship, and the products built beside it.</h2>
        <p>Each one is built and run by our own team in Lahore.</p>
      </div>

      <article class="flag">
        <div class="flag-main">
          <?php echo brand_img('atomshop', 'Atomshop.pk, pay in steps', 'product-logo'); ?>
          <span class="tier">Flagship · Consumer finance</span>
          <h3>Atomshop.pk <span class="status live">Live</span></h3>
          <p class="tagline">Pay in steps.</p>
          <p>Pakistan's installment shopping ecosystem. Customers pick any product and get a plan in seconds. AtomPay gives verified shoppers a spending limit, and a verified seller nearby delivers to their door.</p>
          <div class="flag-links"><a href="<?php echo e(product_url('https://atomshop.pk', 'products')); ?>" target="_blank" rel="noopener">Visit atomshop.pk</a><a href="<?php echo e(product_url('https://atompay.shop', 'products')); ?>" target="_blank" rel="noopener">Visit atompay.shop</a></div>
<?php if ($flagApps = app_links('atomshop', 'Atomshop', 'flag-apps')): ?>
          <div class="get-app"><span class="tier">Get the Atomshop app</span><?php echo $flagApps; ?></div>
<?php endif; ?>
        </div>
        <dl class="flag-facts">
          <div><dt>Plans</dt><dd>3–12 months from 20% advance</dd></div>
          <div><dt>AtomPay</dt><dd>3 months · no advance · 0% markup</dd></div>
          <div><dt>Apps</dt><dd>Atomshop, AtomPay, Atom Seller, AtomBrand</dd></div>
          <div><dt>Financed</dt><dd>Rs. 4.4 crore+ to date</dd></div>
        </dl>
      </article>

      <article class="second">
        <div class="row-main">
          <?php echo brand_img('mark-attendance-mark', 'Mark Attendance logo', 'product-logo square'); ?>
          <span class="tier">Workforce SaaS</span>
          <h3>Mark Attendance <span class="status live">Live</span></h3>
          <p class="tagline">Be on time.</p>
          <p>Face recognition attendance for offices, factories and warehouses. It stops buddy-punching, works offline at the gate, and sets up in a day with any camera device.</p>
          <div class="flag-links"><a href="<?php echo e(product_url('https://markattendance.io', 'products')); ?>" target="_blank" rel="noopener">Visit markattendance.io</a></div>
        </div>
        <div class="ma-visual">
          <div class="scan" aria-label="Example check-in"><div class="face" aria-hidden="true"></div><div class="scan-txt"><b>Ahmed Khan</b><span>09:02 AM · Check-in · 0.8s scan</span></div><span class="verified">Verified</span></div>
          <div class="metrics">
            <div><b>99.8%</b><span>recognition accuracy</span></div>
            <div><b>~2 min</b><span>to enrol an employee</span></div>
            <div><b>1 day</b><span>to set up</span></div>
          </div>
        </div>
      </article>

      <div class="pub">
        <span class="tier">Publishing · Find the niche, then publish it</span>
        <div class="pub-grid">
          <article class="pub-card">
            <?php echo brand_img('kdp-score', 'KDP Score logo', 'pub-logo'); ?>
            <h3>KDP Score</h3>
            <p class="tagline">Know the niche before you write.</p>
            <p>Amazon KDP niche intelligence, powered by AI.</p>
            <a href="<?php echo e(product_url('https://kdpscore.com', 'products')); ?>" target="_blank" rel="noopener">Visit kdpscore.com</a>
          </article>
          <article class="pub-card">
            <?php echo brand_img('publishing-guru', 'Publishing Guru logo', 'pub-logo'); ?>
            <h3>Publishing Guru</h3>
            <p class="tagline">Build, Scale, Flip.</p>
            <p>Done-for-you Amazon KDP publishing: book creation, ads management, and the sale or transfer of KDP accounts. 100+ books launched.</p>
            <a href="<?php echo e(product_url('https://publishinguru.com', 'products')); ?>" target="_blank" rel="noopener">Visit publishinguru.com</a>
          </article>
        </div>
      </div>
    </div>
  </section>

  <section class="section band gitex" id="gitex">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">GITEX Global 2026 · Dubai</span>
        <h2>Meet Atom Services at GITEX.</h2>
        <p>We're in Dubai for the week to meet investors, partners and customers. Book a slot and we'll confirm a time and place.</p>
      </div>
      <div class="gitex-grid">
        <div class="event">
<?php if ($gitexDays > 0): ?>
          <div class="countdown"><b id="days" data-opens="<?php echo GITEX_OPENS_UTC; ?>"><?php echo $gitexDays; ?></b><span><?php echo $gitexDays === 1 ? 'day' : 'days'; ?> until the expo opens</span></div>
<?php else: ?>
          <div class="countdown"><b id="days" data-opens="<?php echo GITEX_OPENS_UTC; ?>">Now</b><span>GITEX Global is open. Come and find us.</span></div>
<?php endif; ?>
          <dl class="event-card">
            <div class="event-row"><dt>Dates</dt><dd>Expo 8–11 December 2026 · Scale Summit 7 December</dd></div>
            <div class="event-row"><dt>Venue</dt><dd>Dubai Exhibition Centre, Expo City Dubai</dd></div>
            <div class="event-row"><dt>Where we are</dt><dd><!-- Add hall and stand number here once confirmed -->Hall and stand to be announced</dd></div>
          </dl>
          <ul class="meet-list">
            <li><span><b>Investors</b> backing embedded finance and BNPL in Pakistan and the Gulf.</span></li>
            <li><span><b>Brands and retailers</b> who want to sell on installments through Atomshop.</span></li>
            <li><span><b>Distribution partners</b> for Mark Attendance in the UAE and GCC.</span></li>
            <li><span><b>Publishers and agencies</b> scaling Amazon KDP catalogues.</span></li>
          </ul>
        </div>

        <div class="form-panel" id="meet">
          <form class="meet" action="forms/contact.php" method="post" novalidate data-ajax>
            <h3>Request a meeting</h3>
            <input type="hidden" name="form" value="meeting">
            <?php echo form_honeypot('m'); ?>
            <div class="two">
              <div class="field"><label for="m-name">Name <abbr title="required">*</abbr></label><input id="m-name" name="name" required maxlength="150" autocomplete="name"></div>
              <div class="field"><label for="m-company">Company</label><input id="m-company" name="company" maxlength="150" autocomplete="organization"></div>
            </div>
            <div class="two">
              <div class="field"><label for="m-email">Email <abbr title="required">*</abbr></label><input id="m-email" name="email" type="email" required maxlength="190" autocomplete="email" inputmode="email"></div>
              <div class="field"><label for="m-day">Preferred day</label>
                <select id="m-day" name="day"><?php foreach (CONTACT_MEETING_DAYS as $day): ?><option><?php echo e($day); ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="field"><label for="m-topic">Interested in</label>
              <select id="m-topic" name="topic"><?php foreach (CONTACT_MEETING_TOPICS as $topic): ?><option><?php echo e($topic); ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="m-note">Note</label><textarea id="m-note" name="note" maxlength="5000" placeholder="What would you like to discuss?"></textarea></div>
            <button class="btn btn-amber" type="submit"><span class="spinner" aria-hidden="true"></span>Request meeting</button>
            <?php echo form_status('meeting'); ?>
            <p class="form-alt">Or email <a href="mailto:<?php echo CONTACT_EMAIL; ?>"><strong><?php echo CONTACT_EMAIL; ?></strong></a>, or <a href="<?php echo e(whatsapp_url('Hi Atom Services, I would like to meet at GITEX 2026.')); ?>" target="_blank" rel="noopener">message us on WhatsApp</a>.</p>
          </form>
          <?php echo form_success('Request received', 'Send another request'); ?>
        </div>
      </div>
    </div>
  </section>

  <section class="section band" id="services">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">Capabilities</span>
        <h2>What we build, for ourselves and for partners.</h2>
        <p>The team that ships Atomshop and Mark Attendance also takes on client projects.</p>
      </div>
      <div class="svc">
        <div><h3><a href="services#custom-software">Custom software</a></h3><p>Business systems built to scale.</p></div>
        <div><h3><a href="services#mobile-apps">Mobile apps</a></h3><p>Android and iOS apps built for performance.</p></div>
        <div><h3><a href="services#saas">SaaS platforms</a></h3><p>Cloud products, launch-ready.</p></div>
        <div><h3><a href="services#ui-ux">UI/UX design</a></h3><p>User-centred digital experiences.</p></div>
      </div>
      <a class="text-link" href="services">See how we work on client projects &rarr;</a>
    </div>
  </section>

<?php include __DIR__ . '/includes/team.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
