<?php

/**
 * contact_process() against a real MySQL database (a throwaway `atomservices_test` schema built from
 * database/schema.sql), with email sending faked. Skipped when MySQL is not reachable.
 */
final class ContactDatabaseTest extends HttpTestCase
{
  private static ?PDO $pdo = null;

  public static function setUpBeforeClass(): void
  {
    $config = __DIR__ . '/../../includes/config.php';
    if (!is_file($config)) {
      return;
    }
    require_once $config;
    try {
      $root = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } catch (PDOException $e) {
      return;
    }
    $schema = file_get_contents(__DIR__ . '/../../database/schema.sql');
    $schema = str_replace('atomservices;', 'atomservices_test;', $schema);
    $schema = str_replace('CREATE DATABASE IF NOT EXISTS atomservices', 'CREATE DATABASE IF NOT EXISTS atomservices_test', $schema);
    $root->exec('DROP DATABASE IF EXISTS atomservices_test');
    $root->exec($schema);
    self::$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=atomservices_test;charset=utf8mb4', DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }

  public static function tearDownAfterClass(): void
  {
    if (self::$pdo) {
      self::$pdo->exec('DROP DATABASE IF EXISTS atomservices_test');
    }
  }

  protected function setUp(): void
  {
    if (!self::$pdo) {
      $this->markTestSkipped('MySQL not reachable with includes/config.php credentials.');
    }
    self::$pdo->exec('TRUNCATE contact_messages');
  }

  /** Same SQL as forms/contact.php. */
  private function process(array $post, callable $send): array
  {
    $pdo = self::$pdo;
    return contact_process(
      $post,
      function ($name, $email, $subject, $message) use ($pdo) {
        $pdo->prepare('INSERT INTO contact_messages (name, email, subject, message, ip_address) VALUES (?, ?, ?, ?, ?)')
            ->execute([$name, $email, $subject, $message, '127.0.0.1']);
        return (int) $pdo->lastInsertId();
      },
      $send,
      function (int $id) use ($pdo) {
        $pdo->prepare('UPDATE contact_messages SET email_sent = 1 WHERE id = ?')->execute([$id]);
      }
    );
  }

  public function testMeetingRequestIsStoredAndMarkedSent(): void
  {
    $sent = [];
    [$status] = $this->process(
      ['form' => 'meeting', 'name' => 'Zara Ahmed', 'email' => 'zara@example.com', 'company' => 'Gulf Retail', 'day' => 'Wed 9 Dec', 'topic' => 'Investing in Atom Services', 'note' => 'Series A', 'utm_source' => 'linkedin'],
      function (...$a) use (&$sent) { $sent[] = $a; }
    );
    $this->assertSame(200, $status);
    $row = self::$pdo->query('SELECT * FROM contact_messages')->fetch();
    $this->assertSame('Zara Ahmed', $row['name']);
    $this->assertSame('GITEX 2026 meeting request: Investing in Atom Services', $row['subject']);
    $this->assertStringContainsString('Preferred day: Wed 9 Dec', $row['message']);
    $this->assertStringContainsString('utm_source: linkedin', $row['message']);
    $this->assertSame(1, (int) $row['email_sent']);
    $this->assertCount(1, $sent);
  }

  public function testStoredEvenWhenEmailFails(): void
  {
    [$status] = $this->process(
      ['form' => 'contact', 'name' => 'Omar', 'email' => 'omar@example.com', 'service' => 'SaaS platform', 'message' => 'Multi-tenant HR product'],
      function () { throw new RuntimeException('SMTP down'); }
    );
    $this->assertSame(200, $status);
    $row = self::$pdo->query('SELECT * FROM contact_messages')->fetch();
    $this->assertSame('Project enquiry: SaaS platform', $row['subject']);
    $this->assertSame(0, (int) $row['email_sent']);
  }

  public function testUnicodeIsStoredIntact(): void
  {
    $this->process(['form' => 'meeting', 'name' => 'عائشہ خان', 'email' => 'a@example.com', 'note' => 'ملاقات 🙂'], fn() => null);
    $row = self::$pdo->query('SELECT name, message FROM contact_messages')->fetch();
    $this->assertSame('عائشہ خان', $row['name']);
    $this->assertStringContainsString('ملاقات 🙂', $row['message']);
  }

  public function testInvalidSubmissionWritesNothing(): void
  {
    $this->process(['form' => 'meeting', 'name' => '', 'email' => 'x'], fn() => null);
    $this->assertSame(0, (int) self::$pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn());
  }
}
