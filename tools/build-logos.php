<?php
/**
 * Builds web-sized product logos from the full-resolution sources (PNG or JPEG) in tools/brand-sources/.
 * Run from the project root after replacing a source logo:
 *   php tools/build-logos.php
 *
 * Outputs to assets/img/brands/:
 *   <name>.png / .webp        full logo, white margins trimmed, max 480px wide
 *   <name>-mark.png / .webp   symbol only (logo with the wordmark cut off), 160px square
 */

if (PHP_SAPI !== 'cli') {
  exit;
}

$root = dirname(__DIR__);
$src = "$root/tools/brand-sources";
$out = "$root/assets/img/brands";
if (!is_dir($out)) {
  mkdir($out, 0775, true);
}

/** Load as true-colour with alpha, flattening nothing. */
function load(string $file): GdImage {
  $im = imagecreatefromstring(file_get_contents($file));
  imagepalettetotruecolor($im);
  imagesavealpha($im, true);
  return $im;
}

/** Bounding box of pixels that are neither transparent nor near-white. */
function content_box(GdImage $im): array {
  // Scan a downscaled copy for speed; sources can be 4500px.
  $w = imagesx($im);
  $h = imagesy($im);
  $scale = min(1, 900 / max($w, $h));
  $sw = max(1, (int) ($w * $scale));
  $sh = max(1, (int) ($h * $scale));
  $small = imagecreatetruecolor($sw, $sh);
  imagealphablending($small, false);
  imagesavealpha($small, true);
  imagecopyresampled($small, $im, 0, 0, 0, 0, $sw, $sh, $w, $h);

  $minX = $sw; $minY = $sh; $maxX = -1; $maxY = -1;
  for ($y = 0; $y < $sh; $y++) {
    for ($x = 0; $x < $sw; $x++) {
      $c = imagecolorat($small, $x, $y);
      $a = ($c >> 24) & 0x7F;
      $r = ($c >> 16) & 0xFF; $g = ($c >> 8) & 0xFF; $b = $c & 0xFF;
      if ($a < 100 && ($r < 235 || $g < 235 || $b < 235)) {
        $minX = min($minX, $x); $maxX = max($maxX, $x);
        $minY = min($minY, $y); $maxY = max($maxY, $y);
      }
    }
  }
  if ($maxX < 0) {
    return [0, 0, $w, $h];
  }
  $pad = 2;
  $x0 = max(0, (int) floor(($minX - $pad) / $scale));
  $y0 = max(0, (int) floor(($minY - $pad) / $scale));
  $x1 = min($w, (int) ceil(($maxX + 1 + $pad) / $scale));
  $y1 = min($h, (int) ceil(($maxY + 1 + $pad) / $scale));
  return [$x0, $y0, $x1 - $x0, $y1 - $y0];
}

function crop(GdImage $im, array $box): GdImage {
  [$x, $y, $w, $h] = $box;
  $c = imagecreatetruecolor($w, $h);
  imagealphablending($c, false);
  imagesavealpha($c, true);
  imagecopy($c, $im, 0, 0, $x, $y, $w, $h);
  return $c;
}

/** Turn near-white pixels transparent so logos sit on any light tile. */
function white_to_alpha(GdImage $im): void {
  imagealphablending($im, false);
  $w = imagesx($im); $h = imagesy($im);
  for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
      $c = imagecolorat($im, $x, $y);
      $r = ($c >> 16) & 0xFF; $g = ($c >> 8) & 0xFF; $b = $c & 0xFF;
      $min = min($r, $g, $b);
      if ($min > 245) {
        imagesetpixel($im, $x, $y, imagecolorallocatealpha($im, 255, 255, 255, 127));
      }
    }
  }
}

/** Fit inside $maxW×$maxH (or a $size square, centred) and save PNG + WebP. */
function save(GdImage $im, string $path, int $maxW, int $maxH, bool $square = false): void {
  $w = imagesx($im); $h = imagesy($im);
  $scale = min($maxW / $w, $maxH / $h, 1);
  $nw = (int) round($w * $scale); $nh = (int) round($h * $scale);
  $cw = $square ? $maxW : $nw; $ch = $square ? $maxH : $nh;
  $dst = imagecreatetruecolor($cw, $ch);
  imagealphablending($dst, false);
  imagesavealpha($dst, true);
  imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 255, 255, 255, 127));
  imagealphablending($dst, true);
  imagecopyresampled($dst, $im, (int) (($cw - $nw) / 2), (int) (($ch - $nh) / 2), 0, 0, $nw, $nh, $w, $h);
  white_to_alpha($dst);
  imagepng($dst, "$path.png", 9);
  imagewebp($dst, "$path.webp", 90);
  printf("%s.png %dx%d (%d KB), .webp (%d KB)\n", basename($path), $cw, $ch, filesize("$path.png") / 1024, filesize("$path.webp") / 1024);
}

// name => fraction of the trimmed logo's height that is the symbol (null = no separate mark)
$logos = [
  'atomshop' => ['mark' => 'left', 'split' => 0.27],   // symbol is the left 27% of a wide logo
  'atompay' => ['mark' => 'top', 'split' => 0.52],     // symbol sits above the wordmark
  'atomseller' => ['mark' => 'top', 'split' => 0.60],
  'atombrand' => ['mark' => 'top', 'split' => 0.60],
  'mark-attendance' => ['mark' => 'top', 'split' => 0.62],
  'publishing-guru' => ['mark' => null],
];

foreach ($logos as $name => $opt) {
  $im = load(is_file("$src/$name.png") ? "$src/$name.png" : "$src/$name.jpg");
  $full = crop($im, content_box($im));
  save($full, "$out/$name", 480, 200);

  if ($opt['mark'] === null) {
    continue;
  }
  $w = imagesx($full); $h = imagesy($full);
  $part = $opt['mark'] === 'top'
    ? crop($full, [0, 0, $w, (int) ($h * $opt['split'])])
    : crop($full, [0, 0, (int) ($w * $opt['split']), $h]);
  $mark = crop($part, content_box($part));
  save($mark, "$out/$name-mark", 160, 160, true);
}
