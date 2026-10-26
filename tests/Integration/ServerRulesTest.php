<?php

use PHPUnit\Framework\Attributes\DataProvider;

/** .htaccess behaviour, 404 handling, sitemap and robots. */
final class ServerRulesTest extends HttpTestCase
{
  public static function privatePaths(): array
  {
    return array_map(fn($p) => [$p], [
      'includes/site.php', 'includes/config.php', 'vendor/autoload.php', 'tests/bootstrap.php',
      'tools/build-images.php', 'docs/TRD.md', 'database/schema.sql', 'composer.json',
      'package.json', 'phpunit.xml', '.htaccess', '.git/config', '.gitignore',
    ]);
  }

  #[DataProvider('privatePaths')]
  public function testPrivateFilesAreBlocked(string $path): void
  {
    $this->assertSame(403, self::http($path)['status'], "$path should not be public");
  }

  public function testWellKnownIsNotBlocked(): void
  {
    // Needed for SSL certificate validation; missing file should be a plain 404, not 403.
    $this->assertSame(404, self::http('.well-known/acme-challenge/test')['status']);
  }

  public function testOldTemplatePagesRedirect(): void
  {
    foreach (['portfolio-details.php', 'service-details.php', 'sarter-page.php'] as $old) {
      $r = self::http($old);
      $this->assertSame(301, $r['status'], $old);
      $this->assertStringEndsWith('/services', $r['redirect']);
    }
  }

  public function testDesignHtmlUrlsRedirectToPhpPages(): void
  {
    foreach (['about', 'contact', 'faqs', 'privacy-policy', 'terms'] as $page) {
      $r = self::http("$page.html");
      $this->assertSame(301, $r['status'], "$page.html");
      $this->assertStringEndsWith("/$page.php", $r['redirect']);
    }
    $r = self::http('index.html');
    $this->assertSame(301, $r['status']);
    $this->assertSame(self::base() . '/', $r['redirect']); // site root, locally or live
  }

  public static function cleanPages(): array
  {
    return array_map(fn($p) => [$p], ['services', 'about', 'contact', 'faqs', 'privacy-policy', 'terms']);
  }

  #[DataProvider('cleanPages')]
  public function testCleanUrlServesThePage(string $page): void
  {
    $r = self::http($page);
    $this->assertSame(200, $r['status'], "/$page");
    $this->assertStringContainsString('<link rel="canonical" href="' . absolute_url($page) . '">', $r['body']);
  }

  #[DataProvider('cleanPages')]
  public function testPhpUrlRedirectsToCleanUrlKeepingQuery(string $page): void
  {
    $r = self::http("$page.php?form_status=ok");
    $this->assertSame(301, $r['status'], "/$page.php");
    $this->assertSame(self::base() . "/$page?form_status=ok", $r['redirect']);
  }

  public function testIndexPhpRedirectsToSiteRoot(): void
  {
    $r = self::http('index.php');
    $this->assertSame(301, $r['status']);
    $this->assertSame(self::base() . '/', $r['redirect']);
  }

  public function testFormHandlerIsNeverRedirected(): void
  {
    // "contact.php" inside forms/ must not be caught by the clean-URL redirect, or every form breaks.
    $this->assertSame(405, self::http('forms/contact.php')['status']);
    $this->assertSame(422, self::http('forms/contact.php', 'POST', ['form' => 'meeting'], false, ['Accept: application/json'])['status']);
  }

  public function testUnknownPageGetsFriendly404(): void
  {
    $r = self::http('this-page-does-not-exist.php');
    $this->assertSame(404, $r['status']);
    $this->assertStringContainsString("We can't find that page", $r['body']);
    $this->assertStringContainsString('<meta name="robots" content="noindex">', $r['body']);
  }

  public function testNested404StillLoadsStyles(): void
  {
    $r = self::http('some/deep/missing/page');
    $this->assertSame(404, $r['status']);
    $x = self::dom($r['body']);
    $base = $x->query('//base/@href')->item(0)->value;
    $css = $x->query('//link[@rel="stylesheet"][contains(@href, "main.css")]/@href')->item(0)->value;
    $this->assertSame(200, self::http(parse_url(self::base(), PHP_URL_SCHEME) . '://' . parse_url(self::base(), PHP_URL_HOST) . $base . $css)['status']);
  }

  public function testSecurityHeaders(): void
  {
    $h = self::http('')['headers'];
    $this->assertSame('nosniff', $h['x-content-type-options'] ?? null);
    $this->assertSame('SAMEORIGIN', $h['x-frame-options'] ?? null);
    $this->assertArrayHasKey('referrer-policy', $h);
  }

  public function testSitemapIsValidAndEveryUrlIsLive(): void
  {
    $r = self::http('sitemap.xml');
    $this->assertSame(200, $r['status']);
    $xml = simplexml_load_string($r['body']);
    $this->assertNotFalse($xml);
    $xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    $locs = $xml->xpath('//s:loc');
    $this->assertNotEmpty($locs);
    foreach ($locs as $loc) {
      $this->assertStringStartsWith(SITE_URL . '/', (string) $loc);
      $local = substr((string) $loc, strlen(SITE_URL) + 1);
      $this->assertSame(200, self::http($local)['status'], "Sitemap URL $loc is not live locally");
    }
  }

  public function testRobotsPointsToSitemap(): void
  {
    $r = self::http('robots.txt');
    $this->assertSame(200, $r['status']);
    $this->assertStringContainsString('Sitemap: ' . SITE_URL . '/sitemap.xml', $r['body']);
    $this->assertStringNotContainsString("Disallow: /\n", $r['body']);
  }
}
