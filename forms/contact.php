<?php
/**
 * Contact form handler.
 * Sends submissions to $receiving_email_address using PHP's built-in mail().
 * Always responds with HTTP 200; the body is either "OK" or an error message,
 * matching the contract expected by assets/vendor/php-email-form/validate.js.
 */

header('Content-Type: text/plain; charset=utf-8');

$receiving_email_address = 'info@atomservices.org';

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

// Strip line breaks from single-line fields to prevent header injection.
$name = str_replace(["\r", "\n"], '', $name);
$subject = str_replace(["\r", "\n"], '', $subject);

$mailSubject = 'New contact message: ' . $subject;
$body = "From: {$name}\nEmail: {$email}\n\nMessage:\n{$message}\n";

$headers = "From: {$receiving_email_address}\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

if (mail($receiving_email_address, $mailSubject, $body, $headers)) {
  echo 'OK';
} else {
  echo 'Message could not be sent. Please try again later.';
}
