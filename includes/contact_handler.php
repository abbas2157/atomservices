<?php
/**
 * Contact / meeting-request logic, kept free of database and mail code so it can be unit tested.
 * forms/contact.php wires these functions to PDO and PHPMailer.
 *
 * Three forms post here:
 *   form=meeting  GITEX meeting request (name, email, company, day, topic, note)
 *   form=contact  project enquiry from the services page (name, email, company, service, message)
 *   form=enquiry  general message from the contact page (name, email, company, topic, message)
 */

const CONTACT_MEETING_DAYS = ['Any day', 'Mon 7 Dec · Summit', 'Tue 8 Dec', 'Wed 9 Dec', 'Thu 10 Dec', 'Fri 11 Dec'];
const CONTACT_MEETING_TOPICS = ['Investing in Atom Services', 'Atomshop & AtomPay partnership', 'Mark Attendance', 'Publishing Guru / KDP Score', 'Something else'];
const CONTACT_SERVICES = ['Custom software', 'Mobile app', 'SaaS platform', 'UI/UX design', 'Not sure yet'];
const CONTACT_ENQUIRY_TOPICS = ['Investment', 'Partnership or brand listing', 'Atomshop.pk or AtomPay', 'Mark Attendance', 'Publishing Guru / KDP Score', 'Careers', 'Media', 'Something else'];
const CONTACT_UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
const CONTACT_EMAIL_FALLBACK = 'info@atomservices.org';

/**
 * Notification recipients from a comma-separated list (RECEIVING_EMAIL in config.php).
 * Invalid addresses and duplicates are dropped so one typo can't stop the others getting the email.
 */
function mail_recipients(string $list): array {
  $out = [];
  foreach (explode(',', $list) as $address) {
    $address = strtolower(trim($address));
    if ($address !== '' && filter_var($address, FILTER_VALIDATE_EMAIL) && !in_array($address, $out, true)) {
      $out[] = $address;
    }
  }
  return $out;
}

/** Collapse a single-line field: trim, strip control characters and newlines (header injection). */
function contact_clean_line($value, int $max): string {
  $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string) $value);
  return mb_substr(trim(preg_replace('/\s+/u', ' ', $value)), 0, $max);
}

/** Trim a multi-line field, keeping newlines. */
function contact_clean_text($value, int $max): string {
  $value = str_replace("\r\n", "\n", (string) $value);
  $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $value);
  return mb_substr(trim($value), 0, $max);
}

/**
 * Validate and normalise a submission.
 * Returns ['errors' => [field => message], 'data' => [...clean values...], 'spam' => bool].
 */
function contact_validate(array $post): array {
  $form = in_array($post['form'] ?? '', ['contact', 'enquiry'], true) ? $post['form'] : 'meeting';
  $data = [
    'form' => $form,
    'name' => contact_clean_line($post['name'] ?? '', 150),
    'email' => contact_clean_line($post['email'] ?? '', 190),
    'company' => contact_clean_line($post['company'] ?? '', 150),
  ];
  $errors = [];

  if ($data['name'] === '') {
    $errors['name'] = 'Please enter your name.';
  }
  if ($data['email'] === '') {
    $errors['email'] = 'Please enter your email address.';
  } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address, like name@company.com.';
  }

  if ($form === 'meeting') {
    $data['day'] = in_array($post['day'] ?? '', CONTACT_MEETING_DAYS, true) ? $post['day'] : 'Any day';
    $data['topic'] = in_array($post['topic'] ?? '', CONTACT_MEETING_TOPICS, true) ? $post['topic'] : 'Something else';
    $data['message'] = contact_clean_text($post['note'] ?? '', 5000);
  } elseif ($form === 'enquiry') {
    $data['topic'] = in_array($post['topic'] ?? '', CONTACT_ENQUIRY_TOPICS, true) ? $post['topic'] : 'Something else';
    $data['message'] = contact_clean_text($post['message'] ?? '', 5000);
    if (mb_strlen($data['message']) < 10) {
      $errors['message'] = 'Please write a short message (at least 10 characters).';
    }
  } else {
    $data['service'] = in_array($post['service'] ?? '', CONTACT_SERVICES, true) ? $post['service'] : 'Not sure yet';
    $data['message'] = contact_clean_text($post['message'] ?? '', 5000);
    if (mb_strlen($data['message']) < 10) {
      $errors['message'] = 'Please tell us a little about your project (at least 10 characters).';
    }
  }

  $data['utm'] = [];
  foreach (CONTACT_UTM_KEYS as $key) {
    $value = contact_clean_line($post[$key] ?? '', 100);
    if ($value !== '') {
      $data['utm'][$key] = $value;
    }
  }

  // Honeypot: the "website" field is hidden from people, so only bots fill it in.
  $spam = trim((string) ($post['website'] ?? '')) !== '';

  return ['errors' => $errors, 'data' => $data, 'spam' => $spam];
}

/** Subject line and plain-text body stored in the database and sent by email. */
function contact_compose(array $data): array {
  if ($data['form'] === 'meeting') {
    $subject = 'GITEX 2026 meeting request: ' . $data['topic'];
    $lines = [
      'Company: ' . ($data['company'] !== '' ? $data['company'] : '-'),
      'Preferred day: ' . $data['day'],
      'Interested in: ' . $data['topic'],
      '',
      $data['message'] !== '' ? $data['message'] : '(no note)',
    ];
  } elseif ($data['form'] === 'enquiry') {
    $subject = 'Website enquiry: ' . $data['topic'];
    $lines = [
      'Company: ' . ($data['company'] !== '' ? $data['company'] : '-'),
      'Topic: ' . $data['topic'],
      '',
      $data['message'],
    ];
  } else {
    $subject = 'Project enquiry: ' . $data['service'];
    $lines = [
      'Company: ' . ($data['company'] !== '' ? $data['company'] : '-'),
      'Service: ' . $data['service'],
      '',
      $data['message'],
    ];
  }

  if ($data['utm']) {
    $lines[] = '';
    $lines[] = 'Campaign tracking:';
    foreach ($data['utm'] as $key => $value) {
      $lines[] = $key . ': ' . $value;
    }
  }

  return ['subject' => $subject, 'message' => implode("\n", $lines)];
}

/**
 * Process a submission. $save(name, email, subject, message): ?int stores it and returns the row id
 * (throws on failure); $send(name, email, subject, message): void emails it (throws on failure);
 * $markSent(id) flags the stored row as emailed.
 *
 * The DB save is authoritative: a submission counts as received once stored, even if the email fails.
 * Returns [httpStatus, responseArray].
 */
function contact_process(array $post, callable $save, callable $send, callable $markSent): array {
  $result = contact_validate($post);

  if ($result['spam']) {
    // Pretend it worked so bots don't learn to skip the honeypot.
    return [200, ['ok' => true, 'message' => contact_success_message('meeting')]];
  }
  if ($result['errors']) {
    return [422, ['ok' => false, 'message' => 'Please fix the highlighted fields.', 'errors' => $result['errors']]];
  }

  $data = $result['data'];
  $mail = contact_compose($data);

  $id = null;
  try {
    $id = $save($data['name'], $data['email'], $mail['subject'], $mail['message']);
  } catch (Throwable $e) {
    error_log('Contact form DB insert failed: ' . $e->getMessage());
  }

  $sent = false;
  try {
    $send($data['name'], $data['email'], $mail['subject'], $mail['message']);
    $sent = true;
    if ($id !== null) {
      $markSent($id);
    }
  } catch (Throwable $e) {
    error_log('Contact form email send failed: ' . $e->getMessage());
  }

  if ($id === null && !$sent) {
    return [500, ['ok' => false, 'message' => 'We could not send your message just now. Please try again, or email ' . CONTACT_EMAIL_FALLBACK . '.']];
  }
  return [200, ['ok' => true, 'message' => contact_success_message($data['form'])]];
}

/**
 * Where a non-JavaScript submission is sent back to. $siteRoot is the site's base path ('/' live,
 * '/atomservices/' on XAMPP). Never built from user input.
 */
function contact_redirect_url(string $form, string $status, string $siteRoot = '/'): string {
  $status = in_array($status, ['ok', 'invalid', 'error'], true) ? $status : 'error';
  switch ($form) {
    case 'contact':
      return $siteRoot . 'services.php?form_status=' . $status . '#contact';
    case 'enquiry':
      return $siteRoot . 'contact.php?form_status=' . $status . '#message';
    default:
      return $siteRoot . '?form_status=' . $status . '#meet';
  }
}

/** Message shown on the page after a non-JavaScript submission, or null if there is none. */
function contact_status_message(?string $status, string $form): ?array {
  switch ($status) {
    case 'ok':
      return ['ok', contact_success_message($form)];
    case 'invalid':
      return ['error', $form === 'meeting'
        ? 'Please add your name and a valid email address, then send again.'
        : 'Please add your name, a valid email address and a short message, then send again.'];
    case 'error':
      return ['error', 'We could not send your message just now. Please try again, or email ' . CONTACT_EMAIL_FALLBACK . '.'];
  }
  return null;
}

function contact_success_message(string $form): string {
  return $form === 'meeting'
    ? 'Thanks. We have your request and will confirm a time and place by email.'
    : 'Thanks. We have your message and will reply by email.';
}
