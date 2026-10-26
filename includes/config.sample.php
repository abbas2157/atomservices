<?php
/**
 * Copy this file to config.php (which is gitignored) and fill in real values.
 * config.php is never committed since it holds credentials.
 */

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'atomservices');
define('DB_USER', 'root');
define('DB_PASS', '');

// --- SMTP (used by PHPMailer in forms/contact.php and forms/newsletter.php) ---
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('SMTP_USERNAME', 'you@example.com');
define('SMTP_PASSWORD', 'your-smtp-password-or-app-password');
define('SMTP_FROM_EMAIL', 'you@example.com');
define('SMTP_FROM_NAME', 'Atom Services');

// Where form notifications are delivered. Several addresses: separate with commas.
define('RECEIVING_EMAIL', 'info@atomservices.org, abbas8156@gmail.com');
