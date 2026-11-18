<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/seo_meta.php';

/** Unit tests for the /admin/seo library (includes/seo_meta.php). No database needed. */
final class SeoMetaTest extends TestCase
{
  public function testSeoPathForMapsCanonicalPaths(): void
  {
    $this->assertSame('/', seo_path_for(''));
    $this->assertSame('/about', seo_path_for('about'));
    $this->assertSame('/privacy-policy', seo_path_for('privacy-policy'));
    $this->assertSame('/about', seo_path_for('/about/'));
  }

  public function testEveryEditablePageHasParseableDefaults(): void
  {
    foreach (SEO_PAGES as $path => [$file, $label]) {
      $this->assertFileExists(__DIR__ . '/../../' . $file, "$label page file");
      $d = seo_page_defaults($path);
      $this->assertNotSame('', $d['title'], "$label: default title parsed from \$pageTitle");
      $this->assertNotSame('', $d['description'], "$label: default description parsed from \$metaDescription");
      $this->assertLessThanOrEqual(200, mb_strlen($d['title']));
      $this->assertStringStartsWith(SITE_URL, $d['og_image']);
    }
  }

  public function testDefaultSchemaIsTheOrganizationSchema(): void
  {
    $d = seo_page_defaults('/');
    $this->assertSame(organization_schema(), json_decode($d['schema'], true));
  }

  public function testLengthStates(): void
  {
    $this->assertSame('empty', seo_length_state(0, SEO_TITLE_RANGE));
    $this->assertSame('warn', seo_length_state(10, SEO_TITLE_RANGE));
    $this->assertSame('ok', seo_length_state(45, SEO_TITLE_RANGE));
    $this->assertSame('warn', seo_length_state(80, SEO_TITLE_RANGE));
    $this->assertSame('ok', seo_length_state(120, SEO_DESCRIPTION_RANGE));
  }

  public function testParseSchemaAcceptsValidObjectAndList(): void
  {
    $error = null;
    $json = seo_parse_schema('{"@context":"https://schema.org","@type":"WebPage","name":"X"}', $error);
    $this->assertNull($error);
    $this->assertStringContainsString('"@type": "WebPage"', (string) $json);

    $json = seo_parse_schema('[{"@type":"Organization"},{"@type":"WebSite"}]', $error);
    $this->assertNull($error);
    $this->assertNotNull($json);
  }

  public function testParseSchemaAcceptsGraphAndEmptyList(): void
  {
    $error = null;
    $this->assertNotNull(seo_parse_schema('{"@context":"https://schema.org","@graph":[{"@type":"WebPage"}]}', $error));
    $this->assertNull($error);
    $this->assertSame('', seo_parse_schema('[]', $error), '[] means "no schema on this page"');
  }

  public function testParseSchemaRejectsInvalidInput(): void
  {
    $error = null;
    $this->assertNull(seo_parse_schema('{not json', $error));
    $this->assertStringContainsString('not valid JSON', $error);

    $error = null;
    $this->assertNull(seo_parse_schema('"just a string"', $error));
    $this->assertNotNull($error);

    $error = null;
    $this->assertNull(seo_parse_schema('{"name":"missing type"}', $error));
    $this->assertStringContainsString('@type', $error);
  }

  public function testBreadcrumbUrlLooksLikeGoogle(): void
  {
    $this->assertSame('atomservices.org', seo_breadcrumb_url('/'));
    $this->assertSame('atomservices.org › services', seo_breadcrumb_url('/services'));
  }

  public function testSeoPageMetaReturnsArrayEvenWithoutOverrides(): void
  {
    $this->assertIsArray(seo_page_meta('/'));
    $this->assertSame([], seo_page_meta('/not-an-editable-page'));
  }
}
