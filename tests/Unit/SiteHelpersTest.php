<?php

use PHPUnit\Framework\TestCase;

final class SiteHelpersTest extends TestCase
{
  public function testEscapesHtml(): void
  {
    $this->assertSame('&lt;b&gt;&quot;A&amp;B&quot;&#039;', e('<b>"A&B"\''));
  }

  public function testProductUrlAddsUtmParameters(): void
  {
    $url = product_url('https://atomshop.pk', 'footer');
    parse_str(parse_url($url, PHP_URL_QUERY), $q);
    $this->assertStringStartsWith('https://atomshop.pk?', $url);
    $this->assertSame('atomservices.org', $q['utm_source']);
    $this->assertSame('referral', $q['utm_medium']);
    $this->assertSame('company_site', $q['utm_campaign']);
    $this->assertSame('footer', $q['utm_content']);
  }

  public function testProductUrlKeepsExistingQuery(): void
  {
    $this->assertStringStartsWith('https://x.test/?a=1&utm_source=', product_url('https://x.test/?a=1'));
  }

  public function testProductUrlWithoutContentOmitsIt(): void
  {
    $this->assertStringNotContainsString('utm_content', product_url('https://x.test'));
  }

  public function testWhatsappUrlUsesNumberAndEncodesText(): void
  {
    $this->assertSame('https://wa.me/16467260635?text=Hi%20there%20%26%20hello', whatsapp_url('Hi there & hello'));
    $this->assertMatchesRegularExpression('/^\d{8,15}$/', WHATSAPP_NUMBER);
  }

  public function testDaysUntilGitex(): void
  {
    $opens = strtotime(GITEX_OPENS_UTC);
    $this->assertSame(1, days_until_gitex($opens - 3600));
    $this->assertSame(43, days_until_gitex($opens - 42 * 86400 - 60));
    $this->assertSame(0, days_until_gitex($opens));
    $this->assertLessThan(0, days_until_gitex($opens + 2 * 86400));
  }

  public function testAbsoluteUrl(): void
  {
    $this->assertSame('https://atomservices.org/', absolute_url(''));
    $this->assertSame('https://atomservices.org/services', absolute_url('/services'));
  }

  public function testSearchIndexEntriesAreComplete(): void
  {
    foreach (['', './'] as $home) {
      $index = search_index($home);
      $this->assertGreaterThan(10, count($index));
      foreach ($index as $item) {
        $this->assertNotEmpty($item['title']);
        $this->assertNotEmpty($item['text']);
        $this->assertNotEmpty($item['url']);
        if ($item['url'][0] === '#' || str_starts_with($item['url'], './#')) {
          $this->assertStringStartsWith($home . '#', $item['url']);
        }
      }
    }
  }

  public function testSearchIndexPointsAtExistingPages(): void
  {
    // Anchors are checked against the rendered pages in PagesTest::testSearchIndexAnchorsExist.
    foreach (search_index('') as $item) {
      // URLs are clean (services, about…); the page behind each one is <name>.php.
      $url = explode('#', $item['url'], 2)[0];
      $page = ($url === '' || $url === './' ? 'index' : $url) . '.php';
      $this->assertFileExists(__DIR__ . '/../../' . $page, "Search result '{$item['title']}' points at a missing page");
    }
  }

  public function testLogoMarkIsDecorativeSvgWithUniqueGradientIds(): void
  {
    $a = logo_mark();
    $b = logo_mark('preloader-mark');
    $this->assertStringContainsString('class="brand-mark"', $a);
    $this->assertStringContainsString('class="preloader-mark"', $b);
    $this->assertStringContainsString('aria-hidden="true"', $a);
    $this->assertSame(9, substr_count($a, '<circle'), 'The mark is a 3×3 dot grid');
    preg_match('/id="([^"]+)"/', $a, $ida);
    preg_match('/id="([^"]+)"/', $b, $idb);
    $this->assertNotSame($ida[1], $idb[1], 'Two marks on one page must not share a gradient id');
    $this->assertStringContainsString('url(#' . $ida[1] . ')', $a);
  }

  public function testAppLinksRendersOnlyPublishedStores(): void
  {
    $html = app_links('atomshop', 'Atomshop');
    $this->assertStringContainsString('href="https://play.google.com/store/apps/details?id=com.ecommerce.atomshop"', $html);
    $this->assertStringContainsString('href="https://apps.apple.com/us/app/atomshop/id6744655747"', $html);
    $this->assertStringContainsString('aria-label="Get Atomshop on Google Play"', $html);
    $this->assertStringContainsString('aria-label="Get Atomshop on App Store"', $html);
    $this->assertSame(2, substr_count($html, 'target="_blank" rel="noopener"'));
    $this->assertSame('', app_links('no-such-app', 'Nothing'), 'Unknown apps render nothing');
  }

  public function testEveryAppLinkIsEmptyOrAStoreUrl(): void
  {
    foreach (APP_LINKS as $app => $stores) {
      $this->assertSame(['android', 'ios'], array_keys($stores), "$app needs android and ios slots");
      if ($stores['android'] !== null) {
        $this->assertStringStartsWith('https://play.google.com/store/apps/details?id=', $stores['android'], $app);
      }
      if ($stores['ios'] !== null) {
        $this->assertMatchesRegularExpression('#^https://apps\.apple\.com/.+/id\d+#', $stores['ios'], $app);
      }
      // An app with no links renders nothing, so no empty or dead buttons reach the page.
      if ($stores['android'] === null && $stores['ios'] === null) {
        $this->assertSame('', app_links($app, $app));
      }
    }
  }

  public function testBrandImageHelper(): void
  {
    $html = brand_img('atompay-mark', 'AtomPay logo', 'x');
    $this->assertStringContainsString('<source srcset="assets/img/brands/atompay-mark.webp" type="image/webp">', $html);
    $this->assertStringContainsString('alt="AtomPay logo" width="160" height="160"', $html);
    $this->assertStringNotContainsString('aria-hidden', $html);
    $this->assertStringContainsString('aria-hidden="true"', brand_img('atompay-mark', ''));
    $this->assertStringContainsString('loading="lazy"', $html);
    $this->assertStringNotContainsString('loading="lazy"', brand_img('atompay-mark', '', '', false), 'Above-the-fold logos must load immediately');
  }

  public function testEveryBrandLogoHasPngAndWebp(): void
  {
    $pngs = glob(__DIR__ . '/../../assets/img/brands/*.png');
    $this->assertGreaterThanOrEqual(11, count($pngs));
    foreach ($pngs as $png) {
      $this->assertFileExists(substr($png, 0, -4) . '.webp');
      $this->assertLessThan(40 * 1024, filesize($png), basename($png) . ' is too large');
    }
  }

  public function testUnconfirmedCompanyDetailsAreNull(): void
  {
    // Guard against placeholder text ever reaching the live site: a detail is either real or null.
    foreach ([COMPANY_SECP_NO, COMPANY_NTN, COMPANY_PK_PHONE] as $value) {
      $this->assertTrue($value === null || (is_string($value) && trim($value) !== ''));
      if ($value !== null) {
        $this->assertDoesNotMatchRegularExpression('/registration no|ntn$|x{3,}|placeholder|todo/i', $value);
      }
    }
  }
}
