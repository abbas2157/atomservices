<?php
http_response_code(404);
$pageTitle = 'Page not found | Atom Services';
$metaDescription = 'This page does not exist. Find Atom Services products, services and GITEX 2026 meetings from the homepage.';
$noindex = true;

include __DIR__ . '/includes/header.php';
?>

  <section class="page-hero band not-found">
    <div class="wrap">
      <span class="eyebrow">Error 404</span>
      <h1>We can't find that page.</h1>
      <p class="hero-lede">It may have moved when we redesigned the site. Try one of these, or search the site.</p>
      <div class="hero-cta">
        <a class="btn btn-amber" href="./">Go to the homepage</a>
        <a class="btn btn-ghost" href="services.php">Our services</a>
        <button class="btn btn-ghost" type="button" data-open-search>Search the site</button>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
