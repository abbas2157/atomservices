<?php
/**
 * Newsletter subscription handler.
 * Sends submissions to $receiving_email_address using PHP's built-in mail().
 * Always responds with HTTP 200; the body is either "OK" or an error message,
 * matching the contract expected by assets/vendor/php-email-form/validate.js.
 */

header('Content-Type: text/plain; charset=utf-8');

$receiving_email_address = 'info@atomservices.org';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  die('Invalid request method.');
}

$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  die('Please provide a valid email address.');
}

$mailSubject = 'New Newsletter Subscription: ' . $email;
$body = "Email: {$email}\n";

$headers = "From: {$receiving_email_address}\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

if (mail($receiving_email_address, $mailSubject, $body, $headers)) {
  echo 'OK';
} else {
  echo 'Subscription could not be sent. Please try again later.';
}
