<?php

use PHPUnit\Framework\TestCase;

defined('SITE_BASE') || define('SITE_BASE', '/atomservices');
require_once __DIR__ . '/../../includes/admin/functions.php';
require_once __DIR__ . '/../../includes/contact_handler.php';

/** Unit tests for the admin view helpers (includes/admin/functions.php). No database needed. */
final class AdminFunctionsTest extends TestCase
{
  protected function setUp(): void
  {
    $_SESSION = [];
  }

  public function testUrlPrefixesTheBasePath(): void
  {
    $this->assertSame('/atomservices/admin', url('/admin'));
  }

  public function testMessageTypeMatchesTheSubjectsContactHandlerComposes(): void
  {
    // The subjects really written by contact_compose() must map back to the right form type.
    $meeting = contact_compose(['form' => 'meeting', 'company' => '', 'day' => 'Any day', 'topic' => 'Mark Attendance', 'message' => '', 'utm' => []]);
    $project = contact_compose(['form' => 'contact', 'company' => '', 'service' => 'Mobile app', 'message' => 'Hi', 'utm' => []]);
    $enquiry = contact_compose(['form' => 'enquiry', 'company' => '', 'topic' => 'Careers', 'message' => 'Hi', 'utm' => []]);

    $this->assertSame('meeting', message_type($meeting['subject']));
    $this->assertSame('contact', message_type($project['subject']));
    $this->assertSame('enquiry', message_type($enquiry['subject']));

    $this->assertSame('GITEX meeting', message_type_label($meeting['subject']));
    $this->assertSame('Project enquiry', message_type_label($project['subject']));
    $this->assertSame('Website enquiry', message_type_label($enquiry['subject']));
  }

  public function testMessageTopicReturnsThePartAfterThePrefix(): void
  {
    $this->assertSame('Mark Attendance', message_topic('GITEX 2026 meeting request: Mark Attendance'));
    $this->assertSame('odd subject', message_topic('odd subject'));
    $this->assertSame('enquiry', message_type('odd subject'), 'unknown subjects fall back to enquiry');
  }

  public function testStatusBadgeWhitelistsStatuses(): void
  {
    $this->assertStringContainsString('badge-new', status_badge('new'));
    $this->assertStringContainsString('badge-replied', status_badge('replied'));
    $this->assertStringContainsString('badge-new', status_badge('<script>'), 'unknown statuses fall back to new');
    $this->assertStringNotContainsString('<script>', status_badge('<script>'));
  }

  public function testInitials(): void
  {
    $this->assertSame('MA', initials('Mudassar Abbas'));
    $this->assertSame('M', initials('mudassar'));
    $this->assertSame('MS', initials('Muhammad Sohaib Chaudhary'), 'only the first two words');
    $this->assertSame('?', initials('  '));
  }

  public function testTimeAgo(): void
  {
    $this->assertSame('Just now', time_ago(date('Y-m-d H:i:s')));
    $this->assertSame('5 min ago', time_ago(date('Y-m-d H:i:s', time() - 300)));
    $this->assertSame('2 h ago', time_ago(date('Y-m-d H:i:s', time() - 7200)));
    $this->assertSame('3 days ago', time_ago(date('Y-m-d H:i:s', time() - 3 * 86400)));
    $this->assertSame('', time_ago(null));
    $this->assertMatchesRegularExpression('/^[A-Z][a-z]{2} \d{1,2}, \d{4}$/', time_ago('2020-01-15 10:00:00'));
  }

  public function testCsrfTokenRoundTrip(): void
  {
    $token = csrf_token();
    $this->assertSame(64, strlen($token));
    $this->assertSame($token, csrf_token(), 'token is stable within a session');
    $this->assertTrue(csrf_valid($token));
    $this->assertFalse(csrf_valid('wrong'));
    $this->assertFalse(csrf_valid(null));
    $this->assertStringContainsString($token, csrf_field());
  }

  public function testAdminIconRendersEveryIconUsedByTheViews(): void
  {
    foreach (['dashboard', 'inbox', 'users', 'globe', 'logout', 'menu', 'x', 'search', 'mail', 'message',
              'sparkle', 'calendar', 'tag', 'arrow-left', 'arrow-right', 'lock', 'file-text', 'code',
              'external', 'check', 'alert', 'reset', 'trash', 'download', 'eye', 'eye-off', 'user',
              'share', 'settings', 'phone-app', 'image', 'phone'] as $name) {
      $svg = admin_icon($name);
      $this->assertStringStartsWith('<svg', $svg, "icon $name");
      $this->assertStringContainsString('aria-hidden="true"', $svg);
    }
  }
}
