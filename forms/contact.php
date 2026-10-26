<?php
/**
 * Contact / meeting-request endpoint.
 * Validation and message composition live in includes/contact_handler.php; this file wires them to
 * the database (authoritative) and SMTP (best effort). Responds with JSON:
 *   {"ok": true, "message": "..."}                      on success
 *   {"ok": false, "message": "...", "errors": {...}}     on validation (422) or server (500) failure
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/contact_handler.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header('Allow: POST');
  echo json_encode(['ok' => false, 'message' => 'Invalid request method.']);
  exit;
}

[$status, $body] = contact_process(
  $_POST,
  function (string $name, string $email, string $subject, string $message): int {
    $stmt = db()->prepare(
      'INSERT INTO contact_messages (name, email, subject, message, ip_address) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, $subject, $message, $_SERVER['REMOTE_ADDR'] ?? null]);
    return (int) db()->lastInsertId();
  },
  function (string $name, string $email, string $subject, string $message): void {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_ENCRYPTION;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    foreach (mail_recipients(RECEIVING_EMAIL) as $to) {
      $mail->addAddress($to);
    }
    $mail->addReplyTo($email, $name);

    $mail->Subject = $subject . ' (' . $name . ')';
    $mail->Body = "From: {$name}\nEmail: {$email}\n\n{$message}\n";
    $mail->send();
  },
  function (int $id): void {
    db()->prepare('UPDATE contact_messages SET email_sent = 1 WHERE id = ?')->execute([$id]);
  }
);

// A plain form post (JavaScript not loaded yet, or disabled) goes back to the page with a status
// the page can show, instead of leaving the visitor on a JSON response.
if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') === false) {
  header('Content-Type: text/html; charset=utf-8', true);
  header('Location: ' . contact_redirect_url($_POST['form'] ?? '', $status === 200 ? 'ok' : ($status === 422 ? 'invalid' : 'error'), rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\') . '/'), true, 303);
  exit;
}

http_response_code($status);
echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
