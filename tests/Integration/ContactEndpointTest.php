<?php

/**
 * forms/contact.php over HTTP. Only paths that never write to the database or send email are hit here;
 * the save/send path is covered against a throwaway database in ContactDatabaseTest.
 */
final class ContactEndpointTest extends HttpTestCase
{
  private const JSON = ['Accept: application/json'];

  private static function post(array $data, array $headers = self::JSON): array
  {
    return self::http('forms/contact.php', 'POST', $data, false, $headers);
  }

  public function testGetIsRejected(): void
  {
    $r = self::http('forms/contact.php');
    $this->assertSame(405, $r['status']);
    $this->assertFalse(json_decode($r['body'], true)['ok']);
  }

  public function testInvalidSubmissionReturnsFieldErrors(): void
  {
    $r = self::post(['form' => 'meeting', 'name' => '', 'email' => 'nope']);
    $this->assertSame(422, $r['status']);
    $this->assertStringContainsString('application/json', $r['headers']['content-type']);
    $json = json_decode($r['body'], true);
    $this->assertFalse($json['ok']);
    $this->assertEqualsCanonicalizing(['name', 'email'], array_keys($json['errors']));
  }

  public function testContactFormRequiresMessage(): void
  {
    $r = self::post(['form' => 'contact', 'name' => 'A', 'email' => 'a@example.com', 'message' => '']);
    $this->assertSame(422, $r['status']);
    $this->assertArrayHasKey('message', json_decode($r['body'], true)['errors']);
  }

  public function testHoneypotSubmissionIsAcceptedSilently(): void
  {
    $r = self::post(['form' => 'meeting', 'name' => 'Bot', 'email' => 'bot@example.com', 'website' => 'http://spam']);
    $this->assertSame(200, $r['status']);
    $this->assertTrue(json_decode($r['body'], true)['ok']);
  }

  // --- Plain form posts (JavaScript not loaded yet, or disabled) ---

  public function testPlainInvalidPostRedirectsBackToMeetingForm(): void
  {
    $r = self::post(['form' => 'meeting', 'name' => '', 'email' => ''], []);
    $this->assertSame(303, $r['status']);
    $this->assertStringEndsWith('/?form_status=invalid#meet', $r['redirect']);

    $page = self::dom(self::http('?form_status=invalid')['body']);
    $msg = $page->query('//form//p[contains(@class, "form-msg")]')->item(0);
    $this->assertStringContainsString('error', $msg->getAttribute('class'));
    $this->assertFalse($msg->hasAttribute('hidden'));
    $this->assertStringContainsString('valid email address', $msg->textContent);
  }

  public function testPlainPostFromServicesRedirectsToServicesForm(): void
  {
    $r = self::post(['form' => 'contact', 'name' => 'Bot', 'email' => 'b@example.com', 'website' => 'spam'], []);
    $this->assertSame(303, $r['status']);
    $this->assertStringEndsWith('/services?form_status=ok#contact', $r['redirect']);

    $msg = self::dom(self::http('services?form_status=ok')['body'])->query('//form//p[contains(@class, "form-msg")]')->item(0);
    $this->assertStringContainsString('ok', $msg->getAttribute('class'));
    $this->assertStringContainsString('reply by email', $msg->textContent);
  }

  public function testEnquiryFormRequiresMessage(): void
  {
    $r = self::post(['form' => 'enquiry', 'name' => 'A', 'email' => 'a@example.com', 'message' => '']);
    $this->assertSame(422, $r['status']);
    $this->assertArrayHasKey('message', json_decode($r['body'], true)['errors']);
  }

  public function testPlainEnquiryPostRedirectsToContactPage(): void
  {
    $r = self::post(['form' => 'enquiry', 'name' => '', 'email' => ''], []);
    $this->assertSame(303, $r['status']);
    $this->assertStringEndsWith('/contact?form_status=invalid#message', $r['redirect']);
    $msg = self::dom(self::http('contact?form_status=invalid')['body'])->query('//form//p[contains(@class, "form-msg")]')->item(0);
    $this->assertStringContainsString('short message', $msg->textContent);
  }

  public function testUnknownStatusShowsNothing(): void
  {
    $msg = self::dom(self::http('?form_status=%3Cscript%3E')['body'])->query('//form//p[contains(@class, "form-msg")]')->item(0);
    $this->assertTrue($msg->hasAttribute('hidden'));
    $this->assertSame('', trim($msg->textContent));
  }
}
