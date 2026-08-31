<?php
/**
 * Contact form handler.
 * Saves every submission to the database, then attempts to send a
 * notification email via SMTP. The DB save is authoritative — a submission
 * is treated as successful once it's stored, even if the email fails, so no
 * message is ever lost to an SMTP hiccup.
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

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $subject === '' || $message === '') {
  die('Please fill in all fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  die('Please provide a valid email address.');
}

$name = str_replace(["\r", "\n"], '', $name);
$subject = str_replace(["\r", "\n"], '', $subject);

try {
  $stmt = db()->prepare(
    'INSERT INTO contact_messages (name, email, subject, message, ip_address) VALUES (?, ?, ?, ?, ?)'
  );
  $stmt->execute([$name, $email, $subject, $message, $_SERVER['REMOTE_ADDR'] ?? null]);
  $messageId = (int) db()->lastInsertId();
  $saved = true;
} catch (PDOException $e) {
  error_log('Contact form DB insert failed: ' . $e->getMessage());
  $saved = false;
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
  $mail->addReplyTo($email, $name);

  $mail->Subject = 'New contact message: ' . $subject;
  $mail->Body = "From: {$name}\nEmail: {$email}\n\nMessage:\n{$message}\n";

  $mail->send();
  $emailSent = true;

  if ($saved) {
    db()->prepare('UPDATE contact_messages SET email_sent = 1 WHERE id = ?')->execute([$messageId]);
  }
} catch (PHPMailerException $e) {
  error_log('Contact form email send failed: ' . $mail->ErrorInfo);
}

if ($saved || $emailSent) {
  echo 'OK';
} else {
  echo 'Message could not be sent. Please try again later.';
}
