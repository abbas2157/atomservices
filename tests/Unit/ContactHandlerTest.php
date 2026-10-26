<?php

use PHPUnit\Framework\TestCase;

final class ContactHandlerTest extends TestCase
{
  private function meeting(array $overrides = []): array
  {
    return $overrides + [
      'form' => 'meeting',
      'name' => 'Sara Khan',
      'email' => 'sara@example.com',
      'company' => 'Acme',
      'day' => 'Tue 8 Dec',
      'topic' => 'Mark Attendance',
      'note' => 'Distribution in the UAE.',
    ];
  }

  private function contact(array $overrides = []): array
  {
    return $overrides + [
      'form' => 'contact',
      'name' => 'Ali Raza',
      'email' => 'ali@example.com',
      'service' => 'Mobile app',
      'message' => 'We need a delivery tracking app.',
    ];
  }

  // --- validation ---

  public function testValidMeetingHasNoErrors(): void
  {
    $r = contact_validate($this->meeting());
    $this->assertSame([], $r['errors']);
    $this->assertFalse($r['spam']);
    $this->assertSame('meeting', $r['data']['form']);
  }

  public function testMissingNameAndEmailAreReported(): void
  {
    $r = contact_validate($this->meeting(['name' => '  ', 'email' => '']));
    $this->assertArrayHasKey('name', $r['errors']);
    $this->assertArrayHasKey('email', $r['errors']);
  }

  public function testInvalidEmailIsReported(): void
  {
    $r = contact_validate($this->meeting(['email' => 'not-an-email']));
    $this->assertStringContainsString('valid email', $r['errors']['email']);
  }

  public function testUnknownFormTypeFallsBackToMeeting(): void
  {
    $r = contact_validate($this->meeting(['form' => 'something']));
    $this->assertSame('meeting', $r['data']['form']);
  }

  public function testMeetingSelectValuesAreWhitelisted(): void
  {
    $r = contact_validate($this->meeting(['day' => 'Sat 12 Dec', 'topic' => '<script>']));
    $this->assertSame('Any day', $r['data']['day']);
    $this->assertSame('Something else', $r['data']['topic']);
  }

  public function testMeetingNoteIsOptional(): void
  {
    $r = contact_validate($this->meeting(['note' => '']));
    $this->assertSame([], $r['errors']);
  }

  public function testContactFormRequiresAMessage(): void
  {
    $r = contact_validate($this->contact(['message' => 'hi']));
    $this->assertArrayHasKey('message', $r['errors']);
  }

  public function testContactServiceIsWhitelisted(): void
  {
    $r = contact_validate($this->contact(['service' => 'Hacking']));
    $this->assertSame('Not sure yet', $r['data']['service']);
  }

  public function testNewlinesAreStrippedFromSingleLineFields(): void
  {
    $r = contact_validate($this->meeting(['name' => "Eve\r\nBcc: victim@example.com"]));
    $this->assertStringNotContainsString("\n", $r['data']['name']);
    $this->assertStringNotContainsString("\r", $r['data']['name']);
  }

  public function testFieldsAreTruncatedToColumnLengths(): void
  {
    $r = contact_validate($this->meeting(['name' => str_repeat('a', 400)]));
    $this->assertSame(150, mb_strlen($r['data']['name']));
  }

  public function testMultiLineMessageKeepsNewlines(): void
  {
    $r = contact_validate($this->contact(['message' => "Line one\r\nLine two is here"]));
    $this->assertSame("Line one\nLine two is here", $r['data']['message']);
  }

  public function testUtmValuesAreCapturedAndUnknownKeysIgnored(): void
  {
    $r = contact_validate($this->meeting(['utm_source' => 'linkedin', 'utm_campaign' => 'gitex', 'utm_evil' => 'x']));
    $this->assertSame(['utm_source' => 'linkedin', 'utm_campaign' => 'gitex'], $r['data']['utm']);
  }

  public function testHoneypotMarksSpam(): void
  {
    $r = contact_validate($this->meeting(['website' => 'http://spam.example']));
    $this->assertTrue($r['spam']);
  }

  public function testEnquiryRequiresMessageAndWhitelistsTopic(): void
  {
    $r = contact_validate(['form' => 'enquiry', 'name' => 'A', 'email' => 'a@example.com', 'topic' => 'Hacking', 'message' => 'hi']);
    $this->assertSame('enquiry', $r['data']['form']);
    $this->assertSame('Something else', $r['data']['topic']);
    $this->assertSame('Please write a short message (at least 10 characters).', $r['errors']['message']);

    $ok = contact_validate(['form' => 'enquiry', 'name' => 'A', 'email' => 'a@example.com', 'topic' => 'Careers', 'message' => 'I would like to apply.']);
    $this->assertSame([], $ok['errors']);
    $this->assertSame('Careers', $ok['data']['topic']);
  }

  public function testEnquiryEmailSubjectNamesTheTopic(): void
  {
    $mail = contact_compose(contact_validate(['form' => 'enquiry', 'name' => 'A', 'email' => 'a@example.com', 'topic' => 'Media', 'message' => 'Interview request for a podcast.'])['data']);
    $this->assertSame('Website enquiry: Media', $mail['subject']);
    $this->assertStringContainsString('Topic: Media', $mail['message']);
    $this->assertStringContainsString('Interview request', $mail['message']);
  }

  // --- recipients ---

  public function testMailRecipientsSplitsCommaSeparatedList(): void
  {
    $this->assertSame(
      ['info@atomservices.org', 'abbas8156@gmail.com'],
      mail_recipients('info@atomservices.org, abbas8156@gmail.com')
    );
  }

  public function testMailRecipientsDropsInvalidBlankAndDuplicateAddresses(): void
  {
    $this->assertSame(
      ['info@atomservices.org', 'abbas8156@gmail.com'],
      mail_recipients(' info@atomservices.org ,, not-an-email, ABBAS8156@gmail.com, info@atomservices.org ')
    );
    $this->assertSame(['one@example.com'], mail_recipients('one@example.com'));
    $this->assertSame([], mail_recipients(''));
  }

  public function testConfiguredRecipientsIncludeBothInboxes(): void
  {
    $config = __DIR__ . '/../../includes/config.sample.php';
    preg_match("/define\('RECEIVING_EMAIL', '([^']*)'\)/", file_get_contents($config), $m);
    $this->assertSame(['info@atomservices.org', 'abbas8156@gmail.com'], mail_recipients($m[1]));
  }

  // --- composition ---

  public function testMeetingEmailContainsAllDetails(): void
  {
    $mail = contact_compose(contact_validate($this->meeting())['data']);
    $this->assertSame('GITEX 2026 meeting request: Mark Attendance', $mail['subject']);
    $this->assertStringContainsString('Company: Acme', $mail['message']);
    $this->assertStringContainsString('Preferred day: Tue 8 Dec', $mail['message']);
    $this->assertStringContainsString('Distribution in the UAE.', $mail['message']);
  }

  public function testEmptyCompanyAndNoteHavePlaceholders(): void
  {
    $mail = contact_compose(contact_validate($this->meeting(['company' => '', 'note' => '']))['data']);
    $this->assertStringContainsString('Company: -', $mail['message']);
    $this->assertStringContainsString('(no note)', $mail['message']);
  }

  public function testContactEmailSubjectNamesTheService(): void
  {
    $mail = contact_compose(contact_validate($this->contact())['data']);
    $this->assertSame('Project enquiry: Mobile app', $mail['subject']);
  }

  public function testUtmIsAppendedToMessage(): void
  {
    $mail = contact_compose(contact_validate($this->meeting(['utm_source' => 'linkedin']))['data']);
    $this->assertStringContainsString("Campaign tracking:\nutm_source: linkedin", $mail['message']);
  }

  public function testNoUtmSectionWithoutUtm(): void
  {
    $mail = contact_compose(contact_validate($this->meeting())['data']);
    $this->assertStringNotContainsString('Campaign tracking', $mail['message']);
  }

  // --- processing ---

  private function run_process(array $post, $save, $send, ?array &$marked = null): array
  {
    $marked = [];
    return contact_process($post, $save, $send, function (int $id) use (&$marked) { $marked[] = $id; });
  }

  public function testSuccessSavesSendsAndMarksSent(): void
  {
    $saved = $sent = [];
    [$status, $body] = $this->run_process(
      $this->meeting(),
      function (...$a) use (&$saved) { $saved[] = $a; return 42; },
      function (...$a) use (&$sent) { $sent[] = $a; },
      $marked
    );
    $this->assertSame(200, $status);
    $this->assertTrue($body['ok']);
    $this->assertCount(1, $saved);
    $this->assertCount(1, $sent);
    $this->assertSame([42], $marked);
    $this->assertSame('Sara Khan', $saved[0][0]);
  }

  public function testEmailFailureStillSucceedsWhenSaved(): void
  {
    [$status, $body] = $this->run_process(
      $this->meeting(),
      fn() => 7,
      function () { throw new RuntimeException('SMTP down'); },
      $marked
    );
    $this->assertSame(200, $status);
    $this->assertTrue($body['ok']);
    $this->assertSame([], $marked);
  }

  public function testDatabaseFailureStillSucceedsWhenEmailed(): void
  {
    [$status, $body] = $this->run_process(
      $this->meeting(),
      function () { throw new PDOException('DB down'); },
      fn() => null,
      $marked
    );
    $this->assertSame(200, $status);
    $this->assertTrue($body['ok']);
    $this->assertSame([], $marked);
  }

  public function testBothFailingReturns500(): void
  {
    [$status, $body] = $this->run_process(
      $this->meeting(),
      function () { throw new PDOException('DB down'); },
      function () { throw new RuntimeException('SMTP down'); }
    );
    $this->assertSame(500, $status);
    $this->assertFalse($body['ok']);
    $this->assertStringContainsString('info@atomservices.org', $body['message']);
  }

  public function testValidationErrorsReturn422WithoutSaving(): void
  {
    $called = false;
    [$status, $body] = $this->run_process(
      $this->meeting(['email' => 'bad']),
      function () use (&$called) { $called = true; return 1; },
      function () use (&$called) { $called = true; }
    );
    $this->assertSame(422, $status);
    $this->assertFalse($body['ok']);
    $this->assertArrayHasKey('email', $body['errors']);
    $this->assertFalse($called);
  }

  public function testSpamLooksSuccessfulButIsDropped(): void
  {
    $called = false;
    [$status, $body] = $this->run_process(
      $this->meeting(['website' => 'x']),
      function () use (&$called) { $called = true; return 1; },
      function () use (&$called) { $called = true; }
    );
    $this->assertSame(200, $status);
    $this->assertTrue($body['ok']);
    $this->assertFalse($called);
  }

  public function testRedirectUrlsForPlainFormPosts(): void
  {
    $this->assertSame('/?form_status=ok#meet', contact_redirect_url('meeting', 'ok'));
    $this->assertSame('/atomservices/services.php?form_status=invalid#contact', contact_redirect_url('contact', 'invalid', '/atomservices/'));
    $this->assertSame('/?form_status=error#meet', contact_redirect_url('https://evil.example', 'javascript:alert(1)'));
    $this->assertSame('/contact.php?form_status=ok#message', contact_redirect_url('enquiry', 'ok'));
  }

  public function testStatusMessages(): void
  {
    $this->assertSame('ok', contact_status_message('ok', 'meeting')[0]);
    $this->assertStringContainsString('time and place', contact_status_message('ok', 'meeting')[1]);
    $this->assertStringContainsString('short message', contact_status_message('invalid', 'contact')[1]);
    $this->assertStringContainsString('short message', contact_status_message('invalid', 'enquiry')[1]);
    $this->assertStringNotContainsString('message', contact_status_message('invalid', 'meeting')[1]);
    $this->assertSame('error', contact_status_message('error', 'contact')[0]);
    $this->assertNull(contact_status_message(null, 'meeting'));
    $this->assertNull(contact_status_message('<script>', 'meeting'));
  }

  public function testSuccessMessageDependsOnForm(): void
  {
    [, $meeting] = $this->run_process($this->meeting(), fn() => 1, fn() => null);
    [, $contact] = $this->run_process($this->contact(), fn() => 1, fn() => null);
    $this->assertStringContainsString('time and place', $meeting['message']);
    $this->assertStringContainsString('reply by email', $contact['message']);
  }
}
