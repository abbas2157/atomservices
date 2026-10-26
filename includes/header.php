<?php
/**
 * Shared <head> + header/nav markup for every page.
 * Expected variables (set before including this file):
 *   $pageTitle        string  full <title> text
 *   $metaDescription  string  meta description
 *   $canonicalPath    string  path for the canonical URL ('' for the homepage)
 */
require_once __DIR__ . '/site.php';
require_once __DIR__ . '/search_index.php';

$pageTitle = $pageTitle ?? SITE_NAME;
$metaDescription = $metaDescription ?? 'Atom Services (Pvt) Ltd builds AI-powered product financing and the software around it: Atomshop.pk, AtomPay, Mark Attendance, KDP Score and Publishing Guru.';
$canonicalPath = $canonicalPath ?? '';
$noindex = $noindex ?? false;

$currentPage = basename($_SERVER['SCRIPT_NAME']);
$home = ($currentPage === 'index.php') ? '' : './';
$canonical = absolute_url($canonicalPath);
$ogImage = absolute_url('assets/img/og-image.png');

$organization = [
  '@context' => 'https://schema.org',
  '@type' => 'Organization',
  'name' => SITE_NAME,
  'alternateName' => 'Atom Services',
  'url' => SITE_URL,
  'logo' => absolute_url('assets/img/apple-touch-icon.png'),
  'email' => CONTACT_EMAIL,
  'telephone' => CONTACT_PHONE,
  'slogan' => 'Smarter finance, better tomorrow.',
  'address' => [
    ['@type' => 'PostalAddress', 'addressLocality' => 'Lahore', 'addressCountry' => 'PK'],
    ['@type' => 'PostalAddress', 'addressLocality' => 'Sheridan', 'addressRegion' => 'WY', 'addressCountry' => 'US'],
  ],
  'sameAs' => [
    'https://www.linkedin.com/company/atomservicesofficial/',
    'https://www.facebook.com/AtomServicesOfficial/',
  ],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?php echo e($pageTitle); ?></title>
  <meta name="description" content="<?php echo e($metaDescription); ?>">
<?php if ($noindex): ?>
  <meta name="robots" content="noindex">
  <?php /* Error pages can be served at any depth, so resolve relative links from the site root. */ ?>
  <base href="<?php echo e(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/'); ?>">
<?php else: ?>
  <link rel="canonical" href="<?php echo e($canonical); ?>">
<?php endif; ?>
  <meta name="theme-color" content="#14213D">

  <!-- Link previews (Open Graph + Twitter/X) -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Atom Services">
  <meta property="og:title" content="<?php echo e($pageTitle); ?>">
  <meta property="og:description" content="<?php echo e($metaDescription); ?>">
  <meta property="og:url" content="<?php echo e($canonical); ?>">
  <meta property="og:image" content="<?php echo e($ogImage); ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="Atom Services: AI-powered product financing, built in Lahore. Meet us at GITEX Global 2026.">
  <meta property="og:locale" content="en_US">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo e($pageTitle); ?>">
  <meta name="twitter:description" content="<?php echo e($metaDescription); ?>">
  <meta name="twitter:image" content="<?php echo e($ogImage); ?>">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon" type="image/png">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Self-hosted fonts (declared in main.css); preload the two used above the fold -->
  <link rel="preload" href="assets/fonts/bricolage-grotesque-800.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="assets/fonts/ibm-plex-sans-400.woff2" as="font" type="font/woff2" crossorigin>

  <link href="assets/css/main.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/main.css'); ?>" rel="stylesheet">
  <noscript><style>#preloader { display: none; }</style></noscript>
  <script type="application/ld+json"><?php echo json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
</head>
<body>

<a class="skip-link" href="#top">Skip to content</a>
<div class="progress" id="progress" aria-hidden="true"></div>
<div id="preloader" aria-hidden="true"><?php echo logo_mark('preloader-mark'); ?></div>

<header class="top band">
  <div class="wrap">
    <a class="brand" href="<?php echo $home === '' ? '#top' : $home; ?>" aria-label="Atom Services home">
      <?php echo logo_mark(); ?>
      Atom Services <small>(PVT) LTD</small>
    </a>
    <div class="top-actions">
      <nav class="nav" id="site-nav" aria-label="Main">
        <a href="<?php echo $home; ?>#products">Products</a>
<?php foreach (['services.php' => 'Services'] as $file => $label): ?>
        <a href="<?php echo $file; ?>"<?php echo $currentPage === $file ? ' aria-current="page"' : ''; ?>><?php echo $label; ?></a>
<?php endforeach; ?>
        <a href="<?php echo $home; ?>#gitex">GITEX 2026</a>
<?php foreach (['about.php' => 'About', 'faqs.php' => 'FAQs', 'contact.php' => 'Contact'] as $file => $label): ?>
        <a href="<?php echo $file; ?>"<?php echo $currentPage === $file ? ' aria-current="page"' : ''; ?>><?php echo $label; ?></a>
<?php endforeach; ?>
      </nav>
      <button class="icon-btn" type="button" data-open-search aria-label="Search the site (press /)"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/></svg></button>
      <a class="btn btn-amber" href="<?php echo $home; ?>#meet">Book a meeting</a>
      <button class="icon-btn menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Open menu"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>

<div class="search-overlay" id="search" role="dialog" aria-modal="true" aria-label="Search the site" hidden>
  <div class="search-box">
    <div class="search-field">
      <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/></svg>
      <label class="sr-only" for="search-input">Search</label>
      <input id="search-input" type="search" placeholder="Search products, services, GITEX…" autocomplete="off">
      <button type="button" class="search-close" data-close-search aria-label="Close search">Esc</button>
    </div>
    <ul class="search-results" aria-live="polite" hidden></ul>
  </div>
</div>
<script type="application/json" id="search-index"><?php echo json_encode(search_index($home), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?></script>

<main id="top" tabindex="-1">
