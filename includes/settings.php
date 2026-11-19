<?php
/**
 * Site settings stored in as_settings (names prefixed 'site_'), editable in /admin/settings.
 * Pattern shared with the Publishing Guru and Mark Attendance admin dashboards.
 *
 * includes/site.php defines the public constants (CONTACT_EMAIL, APP_LINKS, …) from these values:
 * site_setting($key, $default) returns the saved value when one exists — even an intentionally
 * empty one — and the hard-coded default otherwise. Public pages must keep working when the
 * database is down (or includes/config.php does not exist, e.g. in some test environments),
 * so reads fail soft to the defaults.
 */

if (is_file(__DIR__ . '/config.php')) {
  require_once __DIR__ . '/db.php';
}

/** name => value for every saved site setting (without the 'site_' prefix). */
function site_settings(): array {
  static $cache = null;
  if ($cache === null) {
    $cache = [];
    if (function_exists('db_safe')) {
      $rows = db_safe(fn() => db_all("SELECT name, value FROM as_settings WHERE name LIKE 'site\\_%'"), []);
      foreach ($rows as $r) {
        $cache[substr($r['name'], strlen('site_'))] = $r['value'];
      }
    }
  }
  return $cache;
}

/** Saved value for $key; $default when it was never saved. A saved empty value counts as a value. */
function site_setting(string $key, string $default = ''): string {
  $all = site_settings();
  return array_key_exists($key, $all) ? $all[$key] : $default;
}

/**
 * Editable settings for the /admin/settings form, grouped into cards:
 * group => [icon, title, intro, fields], field => [label, type, placeholder, help].
 * Types: url | email | emails | text | textarea | digits | ga4.
 */
function site_setting_fields(): array {
  return [
    'contact' => ['mail', 'Contact details', 'Shown in the footer, on the contact page and in the Organization schema; used by every WhatsApp link.', [
      'contact_email' => ['Public email', 'email', 'info@atomservices.org', 'Shown on the site with a mailto: link.'],
      'contact_phone' => ['Phone (as shown)', 'text', '+1 646 726 0635', 'Displayed next to the phone link in the footer.'],
      'whatsapp_number' => ['WhatsApp number (footer & forms)', 'digits', '16467260635', 'Digits only, with country code — used by footer and form WhatsApp links.'],
      'whatsapp_float_number' => ['WhatsApp number (floating button)', 'digits', '923041129252', 'Digits only, with country code — the green button on every page.'],
      'whatsapp_text' => ['WhatsApp pre-filled message', 'textarea', '', 'The first message visitors see when the chat opens.'],
      'form_recipients' => ['Form submissions go to', 'emails', 'info@atomservices.org, you@gmail.com', 'Comma-separated list. Contact-form messages are emailed here (and saved under Messages).'],
    ]],
    'social' => ['share', 'Social media', 'Full profile links. Shown in the footer and listed in the Organization schema for Google.', [
      'social_linkedin' => ['LinkedIn', 'url', 'https://linkedin.com/company/…', ''],
      'social_facebook' => ['Facebook', 'url', 'https://facebook.com/…', ''],
      'social_twitter' => ['Twitter / X', 'url', 'https://x.com/…', ''],
      'social_instagram' => ['Instagram', 'url', 'https://instagram.com/…', ''],
      'social_youtube' => ['YouTube', 'url', 'https://youtube.com/@…', ''],
    ]],
    'apps' => ['phone-app', 'App store links', 'Store links for the ecosystem apps. Empty = that button is simply not shown.', [
      'app_atomshop_android' => ['Atomshop · Google Play', 'url', 'https://play.google.com/store/apps/details?id=…', ''],
      'app_atomshop_ios' => ['Atomshop · App Store', 'url', 'https://apps.apple.com/…', ''],
      'app_atompay_android' => ['AtomPay · Google Play', 'url', '', ''],
      'app_atompay_ios' => ['AtomPay · App Store', 'url', '', ''],
      'app_atomseller_android' => ['AtomSeller · Google Play', 'url', '', ''],
      'app_atomseller_ios' => ['AtomSeller · App Store', 'url', '', ''],
      'app_atombrand_android' => ['AtomBrands · Google Play', 'url', '', ''],
      'app_atombrand_ios' => ['AtomBrands · App Store', 'url', '', ''],
    ]],
    'google' => ['globe', 'Google', 'Analytics and Search Console.', [
      'ga4_id' => ['GA4 Measurement ID', 'ga4', 'G-XXXXXXXXXX', 'Google Analytics 4. Empty = analytics off. The privacy policy currently says "no analytics" — update it before turning this on.'],
      'google_site_verification' => ['Search Console verification', 'text', 'Abc123…', 'From Search Console → HTML tag method — paste only the content="…" value.'],
    ]],
  ];
}

/** Current effective value of each editable field (saved value, else the site.php default). */
function site_setting_current(): array {
  return [
    'contact_email' => CONTACT_EMAIL,
    'contact_phone' => CONTACT_PHONE,
    'whatsapp_number' => WHATSAPP_NUMBER,
    'whatsapp_float_number' => WHATSAPP_FLOAT_NUMBER,
    'whatsapp_text' => WHATSAPP_TEXT,
    'form_recipients' => FORM_RECIPIENTS,
    'social_linkedin' => SOCIAL_LINKS['linkedin'],
    'social_facebook' => SOCIAL_LINKS['facebook'],
    'social_twitter' => SOCIAL_LINKS['twitter'],
    'social_instagram' => SOCIAL_LINKS['instagram'],
    'social_youtube' => SOCIAL_LINKS['youtube'],
    'app_atomshop_android' => (string) (APP_LINKS['atomshop']['android'] ?? ''),
    'app_atomshop_ios' => (string) (APP_LINKS['atomshop']['ios'] ?? ''),
    'app_atompay_android' => (string) (APP_LINKS['atompay']['android'] ?? ''),
    'app_atompay_ios' => (string) (APP_LINKS['atompay']['ios'] ?? ''),
    'app_atomseller_android' => (string) (APP_LINKS['atomseller']['android'] ?? ''),
    'app_atomseller_ios' => (string) (APP_LINKS['atomseller']['ios'] ?? ''),
    'app_atombrand_android' => (string) (APP_LINKS['atombrand']['android'] ?? ''),
    'app_atombrand_ios' => (string) (APP_LINKS['atombrand']['ios'] ?? ''),
    'ga4_id' => GA4_MEASUREMENT_ID,
    'google_site_verification' => GOOGLE_SITE_VERIFICATION,
  ];
}

/** Validate one field value by type. Returns the cleaned value, or null with $error set. */
function site_setting_clean(string $label, string $type, string $value, ?string &$error): ?string {
  $value = trim($value);
  if ($value === '') {
    return '';
  }
  switch ($type) {
    case 'url':
      if (!preg_match('#^https?://\S+$#', $value)) {
        $error = $label . ' must be a full link starting with https:// — nothing was saved.';
        return null;
      }
      return mb_substr($value, 0, 300);
    case 'email':
      if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $error = $label . ' is not a valid email address — nothing was saved.';
        return null;
      }
      return mb_substr($value, 0, 254);
    case 'emails':
      $clean = [];
      foreach (explode(',', $value) as $address) {
        $address = trim($address);
        if ($address === '') {
          continue;
        }
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
          $error = $label . ' contains an invalid address (' . $address . ') — nothing was saved.';
          return null;
        }
        $clean[] = $address;
      }
      if (!$clean) {
        $error = $label . ' needs at least one email address — nothing was saved.';
        return null;
      }
      return mb_substr(implode(', ', $clean), 0, 500);
    case 'digits':
      $digits = preg_replace('/[\s()+\-]/', '', $value);
      if (!preg_match('/^[0-9]{8,15}$/', $digits)) {
        $error = $label . ' must be 8–15 digits with the country code (e.g. 923001234567) — nothing was saved.';
        return null;
      }
      return $digits;
    case 'ga4':
      $value = strtoupper($value);
      if (!preg_match('/^G-[A-Z0-9]{4,16}$/', $value)) {
        $error = $label . ' must look like G-XXXXXXXXXX — nothing was saved.';
        return null;
      }
      return $value;
    case 'textarea':
      return mb_substr($value, 0, 500);
    default: // text
      return mb_substr(preg_replace('/\s+/u', ' ', $value), 0, 300);
  }
}

/** Save the /admin/settings form. Returns true on success; false with $error set. */
function site_settings_save(array $post, ?string &$error): bool {
  $values = [];
  foreach (site_setting_fields() as [, , , $fields]) {
    foreach ($fields as $key => [$label, $type]) {
      $clean = site_setting_clean($label, $type, (string) ($post[$key] ?? ''), $error);
      if ($clean === null) {
        return false;
      }
      $values[$key] = $clean;
    }
  }

  try {
    $stmt = db()->prepare('REPLACE INTO as_settings (name, value) VALUES (?, ?)');
    foreach ($values as $key => $value) {
      $stmt->execute(['site_' . $key, $value]);
    }
  } catch (PDOException $e) {
    error_log('site_settings_save: ' . $e->getMessage());
    $error = 'Could not save to the database. Please try again.';
    return false;
  }
  return true;
}
