<?php
/**
 * Newsletter subscription handler.
 * Saves the email to the database (a repeat signup is treated as success,
 * not an error), then attempts to send a notification email via SMTP.
 * Always responds with HTTP 200; the body is either "OK" or an error message,
 * matching the contract expected by assets/vendor/php-email-form/validate.js.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  die('Invalid request method.');
}

$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  die('Please provide a valid email address.');
}

$saved = false;
try {
  $stmt = db()->prepare('INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)');
  $stmt->execute([$email]);
  $saved = true;
} catch (PDOException $e) {
  error_log('Newsletter DB insert failed: ' . $e->getMessage());
}

$emailSent = false;
try {
  $mail = new PHPMailer(true);
  $mail->isSMTP();
  $mail->Host = SMTP_HOST;
  $mail->Port = SMTP_PORT;
  $mail->SMTPAuth = true;
  $mail->Username = SMTP_USERNAME;
  $mail->Password = SMTP_PASSWORD;
  $mail->SMTPSecure = SMTP_ENCRYPTION;

  $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
  $mail->addAddress(RECEIVING_EMAIL);
  $mail->addReplyTo($email);

  $mail->Subject = 'New Newsletter Subscription: ' . $email;
  $mail->Body = "Email: {$email}\n";

  $mail->send();
  $emailSent = true;
} catch (PHPMailerException $e) {
  error_log('Newsletter email send failed: ' . $mail->ErrorInfo);
}

if ($saved || $emailSent) {
  echo 'OK';
} else {
  echo 'Subscription could not be saved. Please try again later.';
}
