<?php
/**
 * Site-wide settings and small view helpers. Safe to commit (no secrets; those live in config.php).
 */

const SITE_URL = 'https://atomservices.org';
const SITE_NAME = 'Atom Services (Pvt) Ltd';
const CONTACT_EMAIL = 'info@atomservices.org';
const CONTACT_PHONE = '+1 646 726 0635';
const WHATSAPP_NUMBER = '16467260635'; // digits only, international format, for wa.me links
const WHATSAPP_TEXT = 'Hi Atom Services, I found you on atomservices.org and would like to talk.';

// Company details. A null value is left off the site entirely (no placeholders on live pages);
// fill these in once confirmed and they appear on the About page and in the footer.
const COMPANY_US_ENTITY = 'Atom Services LLC, Sheridan, Wyoming, USA';
const COMPANY_OFFICE = '206 Nasheman Iqbal Cooperative Housing Society, Phase 1, Lahore, Pakistan';
const COMPANY_SECP_NO = null;   // SECP company registration number
const COMPANY_NTN = null;       // National Tax Number
const COMPANY_PK_PHONE = null;  // Pakistan office number, e.g. '+92 42 1234 5678'

// Mobile app store links. A null link is simply not shown, so fill these in as each app is published.
const APP_LINKS = [
  'atomshop' => [
    'android' => 'https://play.google.com/store/apps/details?id=com.ecommerce.atomshop',
    'ios' => 'https://apps.apple.com/us/app/atomshop/id6744655747',
  ],
  'atompay' => ['android' => null, 'ios' => null],
  'atomseller' => ['android' => null, 'ios' => null],
  'atombrand' => ['android' => null, 'ios' => null],
];

// GITEX Global 2026 opens 8 Dec 2026, 10:00 Dubai time (06:00 UTC).
const GITEX_OPENS_UTC = '2026-12-08T06:00:00Z';

/** HTML-escape a value for output. */
function e($value): string {
  return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** WhatsApp click-to-chat link with a prefilled message. */
function whatsapp_url(string $text = WHATSAPP_TEXT): string {
  return 'https://wa.me/' . WHATSAPP_NUMBER . '?text=' . rawurlencode($text);
}

/**
 * Outbound link to one of our own products, tagged with UTM parameters so the product
 * sites can attribute traffic that came from atomservices.org.
 */
function product_url(string $url, string $content = ''): string {
  $params = [
    'utm_source' => 'atomservices.org',
    'utm_medium' => 'referral',
    'utm_campaign' => 'company_site',
  ];
  if ($content !== '') {
    $params['utm_content'] = $content;
  }
  return $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
}

/**
 * The Atom Services mark (the dot grid from the favicon), redrawn as SVG so it stays sharp at any size:
 * a 3×3 grid of dots with two diagonal links, red to orange. Each call gets its own gradient id so
 * several marks can share a page.
 */
function logo_mark(string $class = 'brand-mark'): string {
  static $n = 0;
  $id = 'atom-grad-' . (++$n);
  $dots = '';
  foreach ([5, 15, 25] as $y) {
    foreach ([5, 15, 25] as $x) {
      $dots .= '<circle cx="' . $x . '" cy="' . $y . '" r="3.9"/>';
    }
  }
  return '<svg class="' . e($class) . '" viewBox="0 0 30 30" aria-hidden="true" focusable="false">'
    . '<defs><linearGradient id="' . $id . '" x1="0" y1="30" x2="30" y2="0" gradientUnits="userSpaceOnUse">'
    . '<stop offset="0" stop-color="#BE1E2D"/><stop offset=".55" stop-color="#ED1C24"/><stop offset="1" stop-color="#F26522"/></linearGradient></defs>'
    . '<g fill="url(#' . $id . ')">' . $dots . '</g>'
    . '<g stroke="url(#' . $id . ')" stroke-width="2.4" stroke-linecap="round"><path d="M15 5 5 15M25 15 15 25"/></g>'
    . '</svg>';
}

/**
 * Product logo from assets/img/brands/ (built by tools/build-logos.php) as WebP with a PNG fallback.
 * $file is e.g. 'atompay' or 'atompay-mark'. Pass $alt = '' for a decorative logo next to the name.
 */
function brand_img(string $file, string $alt, string $class = '', bool $lazy = true): string {
  $png = __DIR__ . "/../assets/img/brands/$file.png";
  [$w, $h] = getimagesize($png);
  return '<picture class="' . e($class) . '"' . ($alt === '' ? ' aria-hidden="true"' : '') . '><source srcset="assets/img/brands/' . e($file) . '.webp" type="image/webp">'
    . '<img src="assets/img/brands/' . e($file) . '.png" alt="' . e($alt) . '" width="' . $w . '" height="' . $h . '"' . ($lazy ? ' loading="lazy"' : '') . ' decoding="async"></picture>';
}

/**
 * "Google Play" / "App Store" buttons for one app from APP_LINKS; '' when the app has no store links yet.
 * $name is the app's display name, used in the accessible label.
 */
function app_links(string $app, string $name, string $class = ''): string {
  $links = APP_LINKS[$app] ?? [];
  $stores = [
    'android' => ['Google Play', '<path fill="currentColor" d="M3.6 2.3c-.3.3-.4.7-.4 1.2v17c0 .5.1.9.4 1.2l9.5-9.7-9.5-9.7Zm10.7 10.9 2.5 2.6-10.9 6.2c-.4.2-.8.3-1.1.2l9.5-9Zm3.8-3.9 2.8 1.6c.9.5.9 1.8 0 2.3l-2.8 1.6-2.7-2.8 2.7-2.7Zm-12.2-7 10.9 6.2-2.5 2.6-9.5-9c.3-.1.7 0 1.1.2Z"/>'],
    'ios' => ['App Store', '<path fill="currentColor" d="M16.4 12.6c0-2.6 2.1-3.8 2.2-3.9-1.2-1.8-3.1-2-3.7-2-1.6-.2-3.1.9-3.9.9-.8 0-2-.9-3.4-.9-1.7 0-3.3 1-4.2 2.6-1.8 3.1-.5 7.7 1.3 10.2.9 1.2 1.9 2.6 3.2 2.6 1.3-.1 1.8-.8 3.3-.8 1.6 0 2 .8 3.4.8 1.4 0 2.3-1.3 3.1-2.5 1-1.4 1.4-2.8 1.4-2.9 0 0-2.7-1-2.7-4.1ZM13.8 4.9c.7-.9 1.2-2 1.1-3.2-1 0-2.3.7-3 1.6-.7.8-1.2 2-1.1 3.1 1.1.1 2.3-.6 3-1.5Z"/>'],
  ];
  $html = '';
  foreach ($stores as $store => [$label, $icon]) {
    if (empty($links[$store])) {
      continue;
    }
    $html .= '<a class="store-btn" href="' . e($links[$store]) . '" target="_blank" rel="noopener" aria-label="' . e("Get $name on $label") . '">'
      . '<svg viewBox="0 0 24 24" aria-hidden="true">' . $icon . '</svg><span>' . e($label) . '</span></a>';
  }
  return $html === '' ? '' : '<div class="app-links' . ($class !== '' ? ' ' . e($class) : '') . '">' . $html . '</div>';
}

/** Whole days from $now until GITEX opens; 0 or less means it has started. */
function days_until_gitex(?int $now = null): int {
  $opens = strtotime(GITEX_OPENS_UTC);
  return (int) ceil(($opens - ($now ?? time())) / 86400);
}

/** Absolute URL for a site-relative path, used for canonical and Open Graph tags. */
function absolute_url(string $path = ''): string {
  return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}
