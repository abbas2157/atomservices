<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/settings.php';

/** Unit tests for the /admin/settings library (includes/settings.php). No database writes. */
final class SiteSettingsTest extends TestCase
{
  public function testEveryEditableFieldHasACurrentValue(): void
  {
    $current = site_setting_current();
    foreach (site_setting_fields() as $group => [$icon, $title, $intro, $fields]) {
      foreach ($fields as $key => $def) {
        $this->assertArrayHasKey($key, $current, "field $key ($group) must appear in site_setting_current()");
        $this->assertCount(4, $def, "field $key must define label, type, placeholder, help");
      }
    }
  }

  public function testUrlValidation(): void
  {
    $error = null;
    $this->assertSame('https://x.com/atom', site_setting_clean('X', 'url', ' https://x.com/atom ', $error));
    $this->assertSame('', site_setting_clean('X', 'url', '', $error), 'empty clears the value');
    $this->assertNull(site_setting_clean('X', 'url', 'not-a-url', $error));
    $this->assertStringContainsString('https://', $error);
  }

  public function testEmailValidation(): void
  {
    $error = null;
    $this->assertSame('a@b.com', site_setting_clean('Email', 'email', 'a@b.com', $error));
    $this->assertNull(site_setting_clean('Email', 'email', 'nope', $error));
  }

  public function testEmailListValidation(): void
  {
    $error = null;
    $this->assertSame('a@b.com, c@d.com', site_setting_clean('To', 'emails', ' a@b.com ,c@d.com ', $error));
    $this->assertNull(site_setting_clean('To', 'emails', 'a@b.com, broken', $error));
    $this->assertStringContainsString('broken', $error);
    $error = null;
    $this->assertNull(site_setting_clean('To', 'emails', ' , ', $error), 'separators only is not a list');
  }

  public function testDigitsValidation(): void
  {
    $error = null;
    $this->assertSame('923041129252', site_setting_clean('WA', 'digits', '+92 304-112 9252', $error));
    $this->assertNull(site_setting_clean('WA', 'digits', '12', $error), 'too short');
    $this->assertNull(site_setting_clean('WA', 'digits', 'abc', $error));
  }

  public function testGa4Validation(): void
  {
    $error = null;
    $this->assertSame('G-TB9N4NXGJT', site_setting_clean('GA4', 'ga4', 'g-tb9n4nxgjt', $error), 'uppercased');
    $this->assertNull(site_setting_clean('GA4', 'ga4', 'UA-123456', $error), 'old UA ids rejected');
  }

  public function testTextIsCollapsedAndCapped(): void
  {
    $error = null;
    $this->assertSame('a b', site_setting_clean('T', 'text', "a\n  b", $error));
    $this->assertSame(300, mb_strlen((string) site_setting_clean('T', 'text', str_repeat('x', 400), $error)));
  }

  public function testSiteSettingFallsBackToDefault(): void
  {
    $this->assertSame('fallback', site_setting('a-key-that-is-never-saved-in-tests', 'fallback'));
  }
}
