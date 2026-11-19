<?php
/**
 * Admin dashboard front controller (/admin, /admin/messages, /admin/seo, /admin/settings, …).
 * .htaccess routes every /admin* URL here. Same pattern as the Publishing Guru and
 * Mark Attendance admin dashboards.
 */

// Base path: '' on the live domain, '/atomservices' on local XAMPP.
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/admin.php')), '/');
define('SITE_BASE', ($scriptDir === '' || $scriptDir === '.') ? '' : $scriptDir);

require_once __DIR__ . '/includes/site.php';          // e(), constants, organization_schema()
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/seo_meta.php';
require_once __DIR__ . '/includes/team_data.php';
require_once __DIR__ . '/includes/admin/functions.php';

admin_session_start();
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$adminPath = substr($path, strlen(SITE_BASE . '/admin'));
$adminPath = '/' . trim((string) $adminPath, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// --- Public routes -----------------------------------------------------------
if ($adminPath === '/login') {
  if (admin_user()) {
    redirect('/admin');
  }
  $error = null;
  $username = '';
  if ($method === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    if (!csrf_valid($_POST['csrf'] ?? '')) {
      $error = 'Your session expired. Please try again.';
    } elseif (admin_locked_out()) {
      $error = 'Too many failed attempts. Please wait 15 minutes and try again.';
    } elseif (admin_attempt_login($username, (string) ($_POST['password'] ?? ''))) {
      redirect('/admin');
    } else {
      $error = 'Incorrect username or password.';
    }
  }
  include __DIR__ . '/includes/admin/views/login.php';
  return;
}

// --- Everything below requires a signed-in admin -----------------------------
if (!admin_user()) {
  redirect('/admin/login');
}

if ($adminPath === '/logout' && $method === 'POST') {
  if (csrf_valid($_POST['csrf'] ?? '')) {
    admin_logout();
  }
  redirect('/admin/login');
}

try {
  admin_migrate();
} catch (Throwable $e) {
  error_log('Admin DB: ' . $e->getMessage());
  $messages = [];
  $newCount = 0;
  $view = 'db-error';
  $pageTitle = 'Database not connected';
  include __DIR__ . '/includes/admin/layout.php';
  return;
}

// --- Actions (POST, then redirect) -------------------------------------------
if (preg_match('#^/messages/(\d+)/(status|notes|delete)$#', $adminPath, $m) && $method === 'POST') {
  $id = (int) $m[1];
  if (csrf_valid($_POST['csrf'] ?? '')) {
    if ($m[2] === 'status' && isset(MESSAGE_STATUSES[$_POST['status'] ?? ''])) {
      admin_set_message_status($id, $_POST['status']);
    } elseif ($m[2] === 'notes') {
      db_query('UPDATE contact_messages SET notes = ? WHERE id = ?', [mb_substr(trim((string) ($_POST['notes'] ?? '')), 0, 5000), $id]);
      redirect('/admin/messages/' . $id . '?saved=1');
    } elseif ($m[2] === 'delete') {
      db_query('DELETE FROM contact_messages WHERE id = ?', [$id]);
      redirect('/admin/messages?deleted=1');
    }
  }
  redirect('/admin/messages/' . $id);
}

if (preg_match('#^/subscribers/(\d+)/delete$#', $adminPath, $m) && $method === 'POST') {
  if (csrf_valid($_POST['csrf'] ?? '')) {
    db_query('DELETE FROM newsletter_subscribers WHERE id = ?', [(int) $m[1]]);
  }
  redirect('/admin/subscribers?deleted=1');
}

if (preg_match('#^/team/(\d+)/(delete|move)$#', $adminPath, $m) && $method === 'POST') {
  $id = (int) $m[1];
  if (csrf_valid($_POST['csrf'] ?? '')) {
    if ($m[2] === 'delete') {
      team_delete($id);
      redirect('/admin/team?deleted=1');
    }
    team_move($id, ($_POST['dir'] ?? '') === 'up' ? 'up' : 'down');
  }
  redirect('/admin/team');
}

if ($adminPath === '/messages/export') {
  $rows = admin_messages();
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="atomservices-messages-' . date('Y-m-d') . '.csv"');
  $out = fopen('php://output', 'w');
  fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Urdu/special characters correctly
  $cols = ['id', 'created_at', 'status', 'name', 'email', 'subject', 'message', 'notes', 'ip_address', 'email_sent'];
  fputcsv($out, $cols);
  $safe = fn($s) => preg_match('/^[=+\-@\t]/', (string) $s) && !preg_match('/^\+?[0-9 ()\-]+$/', (string) $s) ? "'" . $s : $s;
  foreach ($rows as $r) {
    fputcsv($out, array_map(fn($c) => $safe($r[$c] ?? ''), $cols));
  }
  exit;
}

if ($adminPath === '/subscribers/export') {
  $rows = admin_subscribers();
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="atomservices-subscribers-' . date('Y-m-d') . '.csv"');
  $out = fopen('php://output', 'w');
  fwrite($out, "\xEF\xBB\xBF");
  fputcsv($out, ['id', 'email', 'created_at']);
  $safe = fn($s) => preg_match('/^[=+\-@\t]/', (string) $s) ? "'" . $s : $s;
  foreach ($rows as $r) {
    fputcsv($out, [$r['id'], $safe($r['email']), $r['created_at']]);
  }
  exit;
}

$messages = admin_messages();
$newCount = count(array_filter($messages, fn($q) => ($q['status'] ?? 'new') === 'new'));

// --- Pages -------------------------------------------------------------------
if ($adminPath === '/') {
  $view = 'dashboard';
  $pageTitle = 'Dashboard';
} elseif ($adminPath === '/messages') {
  $view = 'messages';
  $pageTitle = 'Messages';
} elseif (preg_match('#^/messages/(\d+)$#', $adminPath, $m) && ($message = admin_find_message((int) $m[1]))) {
  // Opening a new message marks it as read.
  if (($message['status'] ?? 'new') === 'new') {
    admin_set_message_status((int) $message['id'], 'read');
    $message['status'] = 'read';
    $newCount = max(0, $newCount - 1);
  }
  $view = 'message';
  $pageTitle = 'Message from ' . $message['name'];
} elseif ($adminPath === '/subscribers') {
  $view = 'subscribers';
  $pageTitle = 'Subscribers';
} elseif ($adminPath === '/team') {
  $view = 'team';
  $pageTitle = 'Team';
} elseif ($adminPath === '/team/new' || (preg_match('#^/team/(\d+)$#', $adminPath, $m) && ($member = team_find((int) $m[1])))) {
  $member ??= null;
  $error = null;
  $posted = null;
  if ($method === 'POST') {
    if (!$_POST && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
      // PHP threw the whole request away: the photo was bigger than post_max_size.
      $error = 'That photo is too large for this server. Please upload one under 8 MB.';
    } elseif (!csrf_valid($_POST['csrf'] ?? '')) {
      $error = 'Your session expired. Please try again.';
    } else {
      $savedId = team_save($member['id'] ?? null, $_POST, $_FILES['photo'] ?? null, $error);
      if ($savedId && !$error) {
        redirect('/admin/team/' . $savedId . '?saved=1');
      }
      if ($savedId) {
        // The details were saved and only the photo failed: keep editing that row.
        $member = team_find((int) $savedId);
        $error = 'The details were saved. ' . $error;
      }
    }
    $posted = $_POST;
  }
  $view = 'team-edit';
  $pageTitle = $member ? 'Edit ' . $member['name'] : 'Add team member';
} elseif ($adminPath === '/seo') {
  $view = 'seo';
  $pageTitle = 'SEO';
} elseif ($adminPath === '/seo/edit' && isset(SEO_PAGES[$seoPath = (string) ($_GET['path'] ?? '')])) {
  $error = null;
  $posted = null;
  if ($method === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? '')) {
      $error = 'Your session expired. Please try again.';
    } elseif (seo_save($seoPath, $_POST, $error)) {
      redirect('/admin/seo/edit?path=' . rawurlencode($seoPath) . '&saved=1');
    }
    $posted = $_POST;
  }
  $view = 'seo-edit';
  $pageTitle = 'SEO: ' . SEO_PAGES[$seoPath][1];
} elseif ($adminPath === '/settings') {
  $error = null;
  if ($method === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? '')) {
      $error = 'Your session expired. Please try again.';
    } elseif (site_settings_save($_POST, $error)) {
      redirect('/admin/settings?saved=1');
    }
  }
  $view = 'settings';
  $pageTitle = 'Site Settings';
} else {
  http_response_code(404);
  $view = 'not-found';
  $pageTitle = 'Page not found';
}

include __DIR__ . '/includes/admin/layout.php';
