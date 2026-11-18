<?php

/**
 * Integration tests for the /admin dashboard routes, run against the live site over HTTP.
 * They never sign in and never write anything: only the public surface is checked
 * (redirects, the login page, robots/noindex protection, and the generated sitemap).
 */
final class AdminTest extends HttpTestCase
{
  public function testAdminRedirectsToLoginWhenSignedOut(): void
  {
    foreach (['admin', 'admin/messages', 'admin/subscribers', 'admin/seo', 'admin/settings'] as $path) {
      $r = self::http($path);
      $this->assertSame(303, $r['status'], "/$path must redirect");
      $this->assertStringContainsString('/admin/login', (string) $r['headers']['location'], "/$path redirects to the login page");
    }
  }

  public function testLoginPageRendersWithCsrfAndPasswordField(): void
  {
    $r = self::http('admin/login');
    $this->assertSame(200, $r['status']);
    $x = self::dom($r['body']);
    $this->assertSame(1, $x->query('//input[@name="password"]')->length);
    $this->assertSame(1, $x->query('//input[@name="username"]')->length);
    $this->assertGreaterThanOrEqual(1, $x->query('//input[@name="csrf"]')->length);
  }

  public function testAdminIsHiddenFromSearchEngines(): void
  {
    $r = self::http('admin/login');
    $this->assertStringContainsString('noindex', $r['headers']['x-robots-tag'] ?? '', 'X-Robots-Tag header');
    $this->assertStringContainsString('noindex', $r['body'], 'robots meta tag');

    $robots = self::http('robots.txt');
    $this->assertStringContainsString('Disallow: /admin', $robots['body']);
  }

  public function testAdminPostActionsRequireLogin(): void
  {
    $r = self::http('admin/messages/1/delete', 'POST', ['csrf' => 'x']);
    $this->assertSame(303, $r['status']);
    $this->assertStringContainsString('/admin/login', (string) $r['headers']['location']);
  }

  public function testDataFolderIsBlocked(): void
  {
    $this->assertSame(403, self::http('data/')['status']);
    $this->assertSame(403, self::http('data/ratelimit/x')['status']);
  }

  public function testGeneratedSitemapListsEveryPublicPage(): void
  {
    $r = self::http('sitemap.xml');
    $this->assertSame(200, $r['status']);
    $this->assertStringContainsString('application/xml', $r['headers']['content-type'] ?? '');
    $xml = @simplexml_load_string($r['body']);
    $this->assertNotFalse($xml, 'sitemap is valid XML');
    $locs = array_map(fn($u) => (string) $u->loc, iterator_to_array($xml->url, false));
    // A page hidden via /admin/seo would legitimately drop out; the default state lists all seven.
    foreach (['/', '/services', '/about', '/contact', '/faqs', '/privacy-policy', '/terms'] as $path) {
      $expected = 'https://atomservices.org' . ($path === '/' ? '/' : $path);
      $this->assertContains($expected, $locs, "$expected must be in the sitemap (is it hidden in /admin/seo?)");
    }
    foreach ($xml->url as $u) {
      $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', (string) $u->lastmod);
    }
  }

  public function testSeoOverrideTablesDoNotBreakPublicPages(): void
  {
    // With or without the admin tables, every public page must render with a title and canonical.
    $r = self::http('about');
    $this->assertSame(200, $r['status']);
    $x = self::dom($r['body']);
    $this->assertSame(1, $x->query('//title')->length);
    $this->assertSame(1, $x->query('//link[@rel="canonical"]')->length);
    $this->assertSame(1, $x->query('//script[@type="application/ld+json"][contains(text(), "Organization")]')->length);
  }
}
