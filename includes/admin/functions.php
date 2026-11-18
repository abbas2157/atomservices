<?php
/**
 * Admin helpers: session/auth, CSRF, message data and small view helpers.
 * Adapted from the Publishing Guru / Mark Attendance admin dashboards.
 * Expects includes/site.php (for e()) and includes/db.php to be loaded, and SITE_BASE defined.
 */

const MESSAGE_STATUSES = ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied'];
const MESSAGE_TYPES = [
  'meeting' => ['GITEX 2026 meeting request', 'GITEX meeting'],
  'contact' => ['Project enquiry', 'Project enquiry'],
  'enquiry' => ['Website enquiry', 'Website enquiry'],
];
const LOGIN_MAX_FAILS = 8;    // failed logins…
const LOGIN_LOCK_SECS = 900;  // …lock the IP for 15 minutes

// Admin credentials live in includes/config.php (gitignored). Empty hash = login disabled.
defined('ADMIN_USERNAME') || define('ADMIN_USERNAME', 'admin');
defined('ADMIN_PASSWORD_HASH') || define('ADMIN_PASSWORD_HASH', '');
defined('ADMIN_DISPLAY_NAME') || define('ADMIN_DISPLAY_NAME', 'Atom Services Admin');

function url(string $path): string {
  return SITE_BASE . $path;
}

/** Cache-busting URL for a local asset, e.g. admin_asset('/assets/css/admin.css'). */
function admin_asset(string $path): string {
  $file = dirname(__DIR__, 2) . $path;
  return SITE_BASE . $path . '?v=' . (is_file($file) ? filemtime($file) : '1');
}

function redirect(string $path): void {
  header('Location: ' . url($path), true, 303);
  exit;
}

function admin_session_start(): void {
  if (session_status() === PHP_SESSION_ACTIVE) {
    return;
  }
  session_name('as_admin');
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => (SITE_BASE ?: '') . '/admin',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
  session_start();
}

/** Signed-in admin username, or null. */
function admin_user(): ?string {
  return $_SESSION['admin_user'] ?? null;
}

function admin_login_enabled(): bool {
  return ADMIN_PASSWORD_HASH !== '';
}

/** File that counts failed logins per IP (data/ is blocked from the web by .htaccess). */
function admin_fail_file(): string {
  $dir = dirname(__DIR__, 2) . '/data/ratelimit';
  if (!is_dir($dir)) {
    @mkdir($dir, 0750, true);
  }
  return $dir . '/login-' . hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function admin_locked_out(): bool {
  $file = admin_fail_file();
  $fails = is_file($file) ? array_filter(array_map('intval', file($file, FILE_IGNORE_NEW_LINES)), fn($t) => $t > time() - LOGIN_LOCK_SECS) : [];
  return count($fails) >= LOGIN_MAX_FAILS;
}

function admin_attempt_login(string $username, string $password): bool {
  $ok = admin_login_enabled()
    && hash_equals(ADMIN_USERNAME, $username)
    && password_verify($password, ADMIN_PASSWORD_HASH);
  if (!$ok) {
    @file_put_contents(admin_fail_file(), time() . "\n", FILE_APPEND | LOCK_EX);
    sleep(1); // slow down password guessing
    return false;
  }
  @unlink(admin_fail_file());
  session_regenerate_id(true);
  $_SESSION['admin_user'] = $username;
  unset($_SESSION['csrf']);
  return true;
}

function admin_logout(): void {
  $_SESSION = [];
  session_regenerate_id(true);
}

function csrf_token(): string {
  return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_valid($token): bool {
  return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function csrf_field(): string {
  return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** All stored contact messages, newest first. */
function admin_messages(): array {
  return db_all('SELECT * FROM contact_messages ORDER BY created_at DESC, id DESC');
}

function admin_find_message(int $id): ?array {
  return db_one('SELECT * FROM contact_messages WHERE id = ?', [$id]);
}

function admin_set_message_status(int $id, string $status): void {
  db_query('UPDATE contact_messages SET status = ? WHERE id = ?', [$status, $id]);
}

/** All newsletter subscribers, newest first. */
function admin_subscribers(): array {
  return db_all('SELECT * FROM newsletter_subscribers ORDER BY created_at DESC, id DESC');
}

/**
 * Which form a message came from, judged by the subject line the handler composed
 * ('GITEX 2026 meeting request: …', 'Project enquiry: …', 'Website enquiry: …').
 */
function message_type(?string $subject): string {
  foreach (MESSAGE_TYPES as $type => [$prefix]) {
    if (str_starts_with((string) $subject, $prefix)) {
      return $type;
    }
  }
  return 'enquiry';
}

function message_type_label(?string $subject): string {
  return MESSAGE_TYPES[message_type($subject)][1];
}

/** The part after the composed prefix, e.g. the chosen topic or service. */
function message_topic(?string $subject): string {
  $subject = (string) $subject;
  $pos = strpos($subject, ': ');
  return $pos !== false ? substr($subject, $pos + 2) : $subject;
}

function status_badge(?string $status): string {
  $status = isset(MESSAGE_STATUSES[$status]) ? $status : 'new';
  return '<span class="badge badge-' . $status . '">' . MESSAGE_STATUSES[$status] . '</span>';
}

/** "Just now", "5 min ago", "3 days ago", or a date for anything older than a week. */
function time_ago(?string $datetime): string {
  $ts = $datetime ? strtotime($datetime) : false;
  if (!$ts) {
    return '';
  }
  $diff = time() - $ts;
  if ($diff < 60) return 'Just now';
  if ($diff < 3600) return floor($diff / 60) . ' min ago';
  if ($diff < 86400) return floor($diff / 3600) . ' h ago';
  if ($diff < 604800) return ($d = floor($diff / 86400)) . ($d == 1 ? ' day ago' : ' days ago');
  return date('M j, Y', $ts);
}

function initials(string $name): string {
  $parts = preg_split('/\s+/', trim($name)) ?: [];
  $letters = array_map(fn($p) => mb_substr($p, 0, 1), array_slice($parts, 0, 2));
  return mb_strtoupper(implode('', $letters)) ?: '?';
}

/** Lucide-style stroke icons used across the admin UI. */
function admin_icon(string $name, string $class = 'icon'): string {
  $paths = [
    'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
    'inbox' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
    'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    'globe' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
    'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
    'menu' => '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
    'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
    'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
    'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
    'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
    'message' => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
    'sparkle' => '<path d="M12 3l1.9 5.8L20 10.7l-5.8 1.9L12 18.5l-1.9-5.9L4 10.7l6.1-1.9z"/>',
    'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
    'tag' => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>',
    'arrow-left' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
    'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
    'lock' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
    'file-text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
    'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21"/>',
    'code' => '<path d="m16 18 6-6-6-6"/><path d="m8 6-6 6 6 6"/>',
    'external' => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
    'check' => '<path d="M20 6 9 17l-5-5"/>',
    'alert' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
    'reset' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>',
    'trash' => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
    'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
    'eye' => '<path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.88 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.88 0"/><circle cx="12" cy="12" r="3"/>',
    'eye-off' => '<path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c4.67 0 8.6 3 9.94 7a10.76 10.76 0 0 1-1.44 2.49"/><path d="M14.08 14.16a3 3 0 0 1-4.24-4.24"/><path d="M17.48 17.5A10.74 10.74 0 0 1 12 19c-4.67 0-8.6-3-9.94-7a10.76 10.76 0 0 1 4.46-5.51"/><path d="m2 2 20 20"/>',
    'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'share' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.59 13.51 6.83 3.98"/><path d="m15.41 6.51-6.82 3.98"/>',
    'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
    'phone-app' => '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/>',
  ];
  return '<svg xmlns="http://www.w3.org/2000/svg" class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[$name] . '</svg>';
}
