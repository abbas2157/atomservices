<?php
/**
 * Shared <head> + header/nav markup for every page.
 * Expected variables (set before including this file):
 *   $pageTitle        string  full <title> text
 *   $metaDescription  string  meta description
 *   $bodyClass        string  class applied to <body>
 */
$pageTitle = $pageTitle ?? 'Atom Services Private Limited';
$metaDescription = $metaDescription ?? 'Atom Services (Pvt) Ltd builds custom software, mobile apps and scalable SaaS products for growing businesses.';
$bodyClass = $bodyClass ?? '';

// Per-page vendor asset flags, set before including this file. Only load what
// a given page actually uses instead of shipping every vendor library everywhere.
$needsGlightbox = $needsGlightbox ?? false;
$needsPureCounter = $needsPureCounter ?? false;
$needsIsotope = $needsIsotope ?? false;
$needsSwiper = $needsSwiper ?? false;

$currentPage = basename($_SERVER['SCRIPT_NAME']);
$home = ($currentPage === 'index.php') ? '' : 'index.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
  <meta name="keywords" content="Atom Services, custom software development, mobile app development, SaaS products, Atomshop.pk">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Raleway:wght@400;500;600;700&family=Ubuntu:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
<?php if ($needsGlightbox): ?>
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
<?php endif; ?>
<?php if ($needsSwiper): ?>
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
<?php endif; ?>

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="<?php echo htmlspecialchars($bodyClass); ?>">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="assets/img/logo.png" alt="Atom Services" width="140" height="40">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="<?php echo $home; ?>#hero"<?php echo $currentPage === 'index.php' ? ' class="active"' : ''; ?>>Home</a></li>
          <li><a href="<?php echo $home; ?>#about">About Us</a></li>
          <li><a href="<?php echo $home; ?>#services">Services</a></li>
          <li><a href="<?php echo $home; ?>#portfolio">Portfolio</a></li>
          <li><a href="<?php echo $home; ?>#team">Team</a></li>
          <li><a href="<?php echo $home; ?>#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="<?php echo $home; ?>#about">Get Started</a>

    </div>
  </header>

  <main class="main">
