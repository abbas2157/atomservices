<?php
/**
 * Set (or change) the /admin login. Run from the project root:
 *
 *   php tools/set-admin-password.php <username> <password> [display name]
 *
 * Writes ADMIN_USERNAME, ADMIN_PASSWORD_HASH and ADMIN_DISPLAY_NAME into includes/config.php
 * (gitignored). Only the password_hash() is stored, never the plain password.
 */

if (PHP_SAPI !== 'cli') {
  http_response_code(403);
  exit("Run this from the command line.\n");
}

$username = $argv[1] ?? '';
$password = $argv[2] ?? '';
$display = $argv[3] ?? 'Atom Services Admin';

if ($username === '' || $password === '') {
  fwrite(STDERR, "Usage: php tools/set-admin-password.php <username> <password> [display name]\n");
  exit(1);
}
if (strlen($password) < 8) {
  fwrite(STDERR, "Please use a password of at least 8 characters.\n");
  exit(1);
}

$config = dirname(__DIR__) . '/includes/config.php';
if (!is_file($config)) {
  fwrite(STDERR, "includes/config.php not found. Copy includes/config.sample.php to includes/config.php first.\n");
  exit(1);
}

$src = (string) file_get_contents($config);
// Drop any previous admin block/lines, then append the new one.
$src = (string) preg_replace("/\n?\/\/ --- Admin dashboard.*?(?=\n\/\/|\n?\$)/s", '', $src);
$src = (string) preg_replace("/\n?define\('ADMIN_(USERNAME|PASSWORD_HASH|DISPLAY_NAME)'[^;]*;\s*/", "\n", $src);
$src = rtrim($src) . "\n\n// --- Admin dashboard (/admin). Set with: php tools/set-admin-password.php ---\n"
  . "define('ADMIN_USERNAME', " . var_export($username, true) . ");\n"
  . "define('ADMIN_PASSWORD_HASH', " . var_export(password_hash($password, PASSWORD_DEFAULT), true) . ");\n"
  . "define('ADMIN_DISPLAY_NAME', " . var_export($display, true) . ");\n";

file_put_contents($config, $src);
echo "Admin login updated. Sign in at /admin/login as '{$username}'.\n";
