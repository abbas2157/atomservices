<?php

use PHPUnit\Framework\Attributes\DataProvider;

/** Renders each public page through Apache and checks structure, SEO tags and every internal link. */
final class PagesTest extends HttpTestCase
{
  public static function pages(): array
  {
    return [
      'home' => [''],
      'services' => ['services'],
      'about' => ['about'],
      'contact' => ['contact'],
      'faqs' => ['faqs'],
      'privacy' => ['privacy-policy'],
      'terms' => ['terms'],
    ];
  }

  #[DataProvider('pages')]
  public function testPageRendersWithoutPhpErrors(string $path): void
  {
    $r = self::http($path);
    $this->assertSame(200, $r['status']);
    $this->assertStringContainsString('text/html', $r['headers']['content-type'] ?? '');
    $this->assertDoesNotMatchRegularExpression('/<b>(Warning|Notice|Fatal error|Deprecated)<\/b>/', $r['body']);
    $this->assertStringEndsWith("</html>\n", $r['body']);
  }

  #[DataProvider('pages')]
  public function testExactlyOneH1(string $path): void
  {
    $x = self::dom(self::http($path)['body']);
    $this->assertSame(1, $x->query('//h1')->length);
    $this->assertGreaterThan(10, strlen(trim($x->query('//h1')->item(0)->textContent)));
  }

  #[DataProvider('pages')]
  public function testSeoAndLinkPreviewTags(string $path): void
  {
    $x = self::dom(self::http($path)['body']);
    $title = trim($x->query('//title')->item(0)->textContent);
    $this->assertNotEmpty($title);
    $this->assertLessThanOrEqual(90, mb_strlen($title));

    $desc = $x->query('//meta[@name="description"]/@content')->item(0)->value;
    $this->assertGreaterThan(70, mb_strlen($desc));
    $this->assertLessThanOrEqual(200, mb_strlen($desc));

    $canonical = $x->query('//link[@rel="canonical"]/@href')->item(0)->value;
    $this->assertSame(absolute_url($path), $canonical);

    foreach (['og:title', 'og:description', 'og:image', 'og:url', 'og:type'] as $prop) {
      $this->assertSame(1, $x->query("//meta[@property='$prop']")->length, "Missing $prop");
    }
    $this->assertSame('summary_large_image', $x->query('//meta[@name="twitter:card"]/@content')->item(0)->value);
  }

  #[DataProvider('pages')]
  public function testStructuredDataIsValidJson(string $path): void
  {
    $x = self::dom(self::http($path)['body']);
    $ld = json_decode($x->query('//script[@type="application/ld+json"]')->item(0)->textContent, true);
    $this->assertSame('Organization', $ld['@type']);
    $this->assertSame(SITE_URL, $ld['url']);

    $index = json_decode($x->query('//script[@id="search-index"]')->item(0)->textContent, true);
    $this->assertIsArray($index);
    $this->assertNotEmpty($index);
  }

  #[DataProvider('pages')]
  public function testIdsAreUnique(string $path): void
  {
    $ids = [];
    foreach (self::dom(self::http($path)['body'])->query('//@id') as $attr) {
      $ids[] = $attr->value;
    }
    $this->assertSame([], array_values(array_unique(array_diff_assoc($ids, array_unique($ids)))));
  }

  #[DataProvider('pages')]
  public function testEveryImageHasAltText(string $path): void
  {
    $x = self::dom(self::http($path)['body']);
    foreach ($x->query('//img') as $img) {
      $this->assertTrue($img->hasAttribute('alt'), 'Image without alt: ' . $img->getAttribute('src'));
      // An empty alt is only allowed on decorative images hidden from screen readers.
      $decorative = $x->query('ancestor-or-self::*[@aria-hidden="true"]', $img)->length > 0;
      if (!$decorative) {
        $this->assertNotSame('', trim($img->getAttribute('alt')), 'Meaningful image without alt text: ' . $img->getAttribute('src'));
      }
      $this->assertNotSame('', $img->getAttribute('width'), 'Image without width: ' . $img->getAttribute('src'));
    }
    // Inline SVGs are decorative unless labelled: they must be hidden from screen readers or have a label.
    $svgs = $x->query('//svg');
    $this->assertGreaterThan(0, $svgs->length);
    foreach ($svgs as $svg) {
      $hidden = $x->query('ancestor-or-self::*[@aria-hidden="true" or @aria-label or @role="img"]', $svg)->length > 0;
      $this->assertTrue($hidden, 'Unlabelled SVG: ' . substr($x->document->saveHTML($svg), 0, 80));
    }
  }

  #[DataProvider('pages')]
  public function testFormFieldsHaveLabels(string $path): void
  {
    $x = self::dom(self::http($path)['body']);
    foreach ($x->query('//input[not(@type="hidden")] | //select | //textarea') as $field) {
      $id = $field->getAttribute('id');
      $this->assertNotSame('', $id);
      $this->assertSame(1, $x->query("//label[@for='$id']")->length, "No label for #$id");
    }
  }

  #[DataProvider('pages')]
  public function testInPageAnchorsPointToExistingIds(string $path): void
  {
    $x = self::dom(self::http($path)['body']);
    foreach ($x->query('//a[starts-with(@href, "#")]/@href') as $href) {
      $id = substr($href->value, 1);
      $this->assertSame(1, $x->query("//*[@id='$id']")->length, "Broken anchor $href->value");
    }
  }

  #[DataProvider('pages')]
  public function testEveryInternalLinkAndAssetResolves(string $path): void
  {
    $x = self::dom(self::http($path)['body']);
    $urls = [];
    foreach ($x->query('//a/@href | //link/@href | //script/@src | //img/@src | //source/@srcset') as $attr) {
      $url = $attr->value;
      if (preg_match('#^(https?:|mailto:|tel:|\#)#', $url)) {
        continue;
      }
      $urls[preg_replace('/#.*$/', '', $url)] = true;
    }
    $this->assertNotEmpty($urls);
    foreach (array_keys($urls) as $url) {
      $target = $url === '' || $url === './' ? '' : $url;
      $status = self::http($target)['status'];
      $this->assertSame(200, $status, "Broken link on /$path: $url ($status)");
    }

    // Anchors on other pages ("./#products", "services#saas") must exist there too.
    foreach ($x->query('//a/@href') as $attr) {
      if (!preg_match('#^(\./|[a-z-]+)\#(.+)$#', $attr->value, $m)) {
        continue;
      }
      $page = $m[1] === './' ? '' : $m[1];
      $other = self::dom(self::http($page)['body']);
      $this->assertSame(1, $other->query("//*[@id='{$m[2]}']")->length, "Broken link $attr->value on /$path");
    }
  }

  public function testOgImageIsTheRightSize(): void
  {
    $r = self::http('assets/img/og-image.png');
    $this->assertSame(200, $r['status']);
    $size = getimagesizefromstring($r['body']);
    $this->assertSame([1200, 630], [$size[0], $size[1]]);
    $this->assertLessThan(300 * 1024, strlen($r['body']));
  }

  public function testImagesAreCompressed(): void
  {
    foreach (glob(__DIR__ . '/../../assets/img/{,team/}*.{jpg,jpeg,png,webp}', GLOB_BRACE) as $file) {
      $this->assertLessThan(120 * 1024, filesize($file), basename($file) . ' is over 120 KB');
    }
  }

  public function testHomepageKeyContent(): void
  {
    $body = self::http('')['body'];
    foreach (['Atomshop.pk', 'AtomPay', 'Mark Attendance', 'KDP Score', 'Publishing Guru', 'GITEX', 'Book a meeting'] as $text) {
      $this->assertStringContainsString($text, $body);
    }
    $this->assertStringContainsString('href="https://wa.me/16467260635?text=', $body);
    $this->assertStringContainsString('utm_source=atomservices.org', $body);
  }

  public function testCountdownIsRenderedServerSide(): void
  {
    $x = self::dom(self::http('')['body']);
    $days = trim($x->query('//*[@id="days"]')->item(0)->textContent);
    $expected = days_until_gitex();
    $this->assertSame($expected > 0 ? (string) $expected : 'Now', $days);
  }

  #[DataProvider('pages')]
  public function testHeadingLevelsNeverSkip(string $path): void
  {
    $previous = 0;
    foreach (self::dom(self::http($path)['body'])->query('//h1|//h2|//h3|//h4|//h5|//h6') as $h) {
      $level = (int) $h->nodeName[1];
      $this->assertLessThanOrEqual($previous + 1, $level, "Heading level jumps to <h$level> at \"" . trim($h->textContent) . "\" on /$path");
      $previous = $level;
    }
  }

  public function testSearchIndexAnchorsExist(): void
  {
    $rendered = [];
    foreach (search_index('') as $item) {
      [$page, $anchor] = array_pad(explode('#', $item['url'], 2), 2, null);
      if ($anchor === null) {
        continue;
      }
      $rendered[$page] ??= self::dom(self::http($page)['body']);
      $this->assertSame(1, $rendered[$page]->query("//*[@id='$anchor']")->length, "Search result '{$item['title']}' points at missing #$anchor on /$page");
    }
  }

  #[DataProvider('pages')]
  public function testNoPlaceholdersOrDraftNotes(string $path): void
  {
    $body = self::http($path)['body'];
    $this->assertStringNotContainsString('class="fill"', $body);
    $this->assertDoesNotMatchRegularExpression('/starting draft|legal adviser|SECP Company Registration No|Registered office street address|\+92 Pakistan office number/i', $body);
  }

  #[DataProvider('pages')]
  public function testNavMarksCurrentPage(string $path): void
  {
    $x = self::dom(self::http($path)['body']);
    $current = $x->query('//nav[@id="site-nav"]/a[@aria-current="page"]');
    if (in_array($path, ['services', 'about', 'faqs', 'contact'], true)) {
      $this->assertSame(1, $current->length);
      $this->assertSame($path, $current->item(0)->getAttribute('href'));
    } else {
      $this->assertSame(0, $current->length);
    }
  }

  public function testFooterLinksToEveryPage(): void
  {
    $x = self::dom(self::http('')['body']);
    foreach (['about', 'services', 'contact', 'faqs', 'privacy-policy', 'terms'] as $page) {
      $this->assertGreaterThan(0, $x->query("//footer//a[@href='$page']")->length, "Footer has no link to $page");
    }
  }

  public function testUnconfirmedCompanyDetailsAreHidden(): void
  {
    $about = self::http('about')['body'];
    $this->assertStringContainsString(COMPANY_OFFICE, $about);
    $this->assertSame(COMPANY_SECP_NO !== null, str_contains($about, 'SECP registration'));
    $this->assertSame(COMPANY_NTN !== null, str_contains($about, 'National Tax Number'));
  }

  public function testFaqStructuredDataMatchesQuestions(): void
  {
    $x = self::dom(self::http('faqs')['body']);
    $schema = null;
    foreach ($x->query('//script[@type="application/ld+json"]') as $s) {
      $json = json_decode($s->textContent, true);
      if (($json['@type'] ?? '') === 'FAQPage') {
        $schema = $json;
      }
    }
    $this->assertNotNull($schema, 'FAQPage structured data missing');
    $questions = [];
    foreach ($x->query('//details/summary') as $summary) {
      $questions[] = trim($summary->textContent);
    }
    $this->assertSame($questions, array_column($schema['mainEntity'], 'name'));
    foreach ($schema['mainEntity'] as $q) {
      $this->assertStringNotContainsString('<a', $q['acceptedAnswer']['text']);
    }
  }

  #[DataProvider('pages')]
  public function testCompanyLogoInHeaderAndFooter(string $path): void
  {
    $x = self::dom(self::http($path)['body']);
    $this->assertSame(1, $x->query('//header//a[contains(@class, "brand")]/*[local-name()="svg" and contains(@class, "brand-mark")]')->length, 'Header logo missing');
    $this->assertSame(1, $x->query('//footer//a[contains(@class, "brand")]/*[local-name()="svg" and contains(@class, "brand-mark")]')->length, 'Footer logo missing');
  }

  public function testHeroBadgesShowProductMarks(): void
  {
    $x = self::dom(self::http('')['body']);
    $this->assertSame(1, $x->query('//*[contains(@class,"toast t1")]//img[contains(@src,"atompay-mark")]')->length);
    $this->assertSame(1, $x->query('//*[contains(@class,"toast t2")]//img[contains(@src,"atomseller-mark")]')->length);
    $this->assertSame(0, $x->query('//*[contains(@class,"toast")]//img[@loading="lazy"]')->length, 'Hero badge logos are above the fold');
  }

  public function testAtomshopAppStoreButtonsOnHomepage(): void
  {
    $x = self::dom(self::http('')['body']);
    // Once in the Atomshop ecosystem card and once in the flagship card.
    $this->assertSame(2, $x->query('//a[contains(@class,"store-btn")][@href="' . APP_LINKS['atomshop']['android'] . '"]')->length);
    $this->assertSame(2, $x->query('//a[contains(@class,"store-btn")][@href="' . APP_LINKS['atomshop']['ios'] . '"]')->length);
    $this->assertSame(0, $x->query('//a[contains(@class,"store-btn")][not(@href) or @href=""]')->length, 'No store button without a link');
  }

  public function testProductLogosAreShown(): void
  {
    $home = self::http('')['body'];
    foreach (['atomshop', 'atompay-mark', 'atomseller-mark', 'atombrand-mark', 'mark-attendance-mark', 'publishing-guru'] as $logo) {
      $this->assertStringContainsString("assets/img/brands/$logo.webp", $home, "$logo missing from homepage");
    }
  }
}
