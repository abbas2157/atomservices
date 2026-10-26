<?php

use PHPUnit\Framework\TestCase;

/** The square crop in tools/build-images.php must keep heads in tall portrait photos. */
final class BuildImagesTest extends TestCase
{
  private static function squareFunction(): void
  {
    if (function_exists('square')) {
      return;
    }
    // Load only the square() helper; running the whole script would rebuild every image.
    $src = file_get_contents(__DIR__ . '/../../tools/build-images.php');
    preg_match('/function square\(.*?\n}\n/s', $src, $m);
    eval($m[0]);
  }

  /** A tall test image: red band across the top 10% (the "head"), white below. */
  private static function portrait(int $w, int $h): GdImage
  {
    $im = imagecreatetruecolor($w, $h);
    imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
    imagefilledrectangle($im, 0, 0, $w - 1, (int) ($h * 0.1), imagecolorallocate($im, 255, 0, 0));
    return $im;
  }

  public function testTallPortraitKeepsTheTop(): void
  {
    self::squareFunction();
    $out = square(self::portrait(100, 140), 100);
    $this->assertSame([100, 100], [imagesx($out), imagesy($out)]);
    // Only 4 px (10% of the 40 px excess) is trimmed from the top, so most of the red band survives.
    $rgb = imagecolorat($out, 50, 3);
    $this->assertGreaterThan(200, ($rgb >> 16) & 0xFF);
    $this->assertLessThan(60, ($rgb >> 8) & 0xFF, 'Top of the portrait was cropped away');
  }

  public function testSquarePhotoIsUnchangedInFraming(): void
  {
    self::squareFunction();
    $out = square(self::portrait(200, 200), 100);
    $this->assertLessThan(60, (imagecolorat($out, 50, 2) >> 8) & 0xFF, 'Square photos should keep their top edge');
  }
}
