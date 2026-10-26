<?php
/**
 * Builds optimised images. Run from the project root after changing team photos:
 *   php tools/build-images.php
 *
 * - Team photos: any assets/img/team/team-N.(jpg|jpeg|png) source is resized to 500×500 and saved as
 *   team-N.jpg (quality 80) and team-N.webp (quality 78). Originals are moved to tools/originals/.
 * - assets/img/og-image.png (1200×630) for link previews, and assets/img/apple-touch-icon.png (180×180).
 *   Text rendering uses Windows fonts; pass --skip-brand on machines without them.
 */

if (PHP_SAPI !== 'cli') {
  exit;
}

$root = dirname(__DIR__);
$teamDir = "$root/assets/img/team";
$originals = "$root/tools/originals";
const TEAM_SIZE = 500;

/**
 * Crop to a square and resize. Wide photos are cropped from the centre; tall portraits keep the top
 * (only 10% of the extra height is taken off the top) so heads are never cut off.
 */
function square(GdImage $src, int $size): GdImage {
  $w = imagesx($src);
  $h = imagesy($src);
  $side = min($w, $h);
  $out = imagecreatetruecolor($size, $size);
  imagecopyresampled($out, $src, 0, 0, (int) (($w - $side) / 2), (int) (($h - $side) * 0.1), $size, $size, $side, $side);
  return $out;
}

foreach (glob("$teamDir/team-*.{jpg,jpeg,png}", GLOB_BRACE) as $file) {
  $info = getimagesize($file);
  $base = preg_replace('/\.(jpe?g|png)$/i', '', basename($file));
  $isFinal = str_ends_with($file, '.jpg') && $info[0] === TEAM_SIZE && $info[1] === TEAM_SIZE;
  if ($isFinal) {
    continue;
  }
  $src = $info[2] === IMAGETYPE_PNG ? imagecreatefrompng($file) : imagecreatefromjpeg($file);
  $out = square($src, TEAM_SIZE);
  imagejpeg($out, "$teamDir/$base.jpg", 80);
  imagewebp($out, "$teamDir/$base.webp", 78);
  if (!is_dir($originals)) {
    mkdir($originals, 0775, true);
  }
  if (!str_ends_with($file, "$base.jpg")) {
    rename($file, "$originals/" . basename($file));
  }
  printf("team: %s -> %s.jpg (%d KB), %s.webp (%d KB)\n", basename($file), $base, filesize("$teamDir/$base.jpg") / 1024, $base, filesize("$teamDir/$base.webp") / 1024);
}

if (in_array('--skip-brand', $argv, true)) {
  exit;
}

$fonts = 'C:/Windows/Fonts';
$bold = "$fonts/segoeuib.ttf";
$semi = "$fonts/seguisb.ttf";
$mono = "$fonts/consola.ttf";
foreach ([$bold, $semi, $mono] as $font) {
  if (!is_file($font)) {
    fwrite(STDERR, "Missing font $font; run with --skip-brand.\n");
    exit(1);
  }
}

function hex(GdImage $im, string $hex, int $alpha = 0) {
  [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');
  return imagecolorallocatealpha($im, $r, $g, $b, $alpha);
}

/** Colour of the mark gradient at a point (0..30 units): dark red bottom-left to orange top-right. */
function mark_colour(float $x, float $y): array {
  $t = max(0, min(1, ($x + (30 - $y)) / 60));
  $stops = [[0, [0xBE, 0x1E, 0x2D]], [0.55, [0xED, 0x1C, 0x24]], [1, [0xF2, 0x65, 0x22]]];
  for ($i = 1; $i < count($stops); $i++) {
    [$t1, $c1] = $stops[$i];
    [$t0, $c0] = $stops[$i - 1];
    if ($t <= $t1) {
      $k = ($t - $t0) / ($t1 - $t0);
      return array_map(fn($a, $b) => (int) round($a + ($b - $a) * $k), $c0, $c1);
    }
  }
  return $stops[2][1];
}

/**
 * The Atom Services mark (same geometry as logo_mark() in includes/site.php): a 3×3 dot grid with two
 * diagonal links. Drawn 4× larger and scaled down, since GD does not antialias filled shapes.
 */
function mark_image(int $size): GdImage {
  $ss = 4;
  $big = $size * $ss;
  $u = $big / 30; // pixels per viewBox unit
  $im = imagecreatetruecolor($big, $big);
  imagealphablending($im, false);
  imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
  imagealphablending($im, true);
  $dot = function (float $x, float $y, float $r) use ($im, $u) {
    [$cr, $cg, $cb] = mark_colour($x, $y);
    imagefilledellipse($im, (int) round($x * $u), (int) round($y * $u), (int) round(2 * $r * $u), (int) round(2 * $r * $u), imagecolorallocate($im, $cr, $cg, $cb));
  };
  foreach ([[15, 5, 5, 15], [25, 15, 15, 25]] as [$x1, $y1, $x2, $y2]) { // links: a row of small discs
    for ($i = 0; $i <= 60; $i++) {
      $dot($x1 + ($x2 - $x1) * $i / 60, $y1 + ($y2 - $y1) * $i / 60, 1.2);
    }
  }
  foreach ([5, 15, 25] as $y) {
    foreach ([5, 15, 25] as $x) {
      $dot($x, $y, 3.9);
    }
  }
  $out = imagecreatetruecolor($size, $size);
  imagealphablending($out, false);
  imagesavealpha($out, true);
  imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
  imagecopyresampled($out, $im, 0, 0, 0, 0, $size, $size, $big, $big);
  return $out;
}

function place_mark(GdImage $dst, int $x, int $y, int $size): void {
  imagealphablending($dst, true);
  imagecopy($dst, mark_image($size), $x, $y, 0, 0, $size, $size);
}

// Link-preview image, 1200×630
$im = imagecreatetruecolor(1200, 630);
imageantialias($im, true);
imagefilledrectangle($im, 0, 0, 1200, 630, hex($im, '#14213D'));
for ($i = 0; $i < 40; $i++) { // soft warm glow behind the mark
  imagefilledellipse($im, 1050, 120, 900 - $i * 20, 900 - $i * 20, hex($im, '#F5A524', 126 - (int) ($i * 0.12)));
}
for ($x = 26; $x < 1200; $x += 32) { // dot grid
  for ($y = 26; $y < 630; $y += 32) {
    imagesetpixel($im, $x, $y, hex($im, '#3A4A6E'));
  }
}
place_mark($im, 880, 185, 260);
place_mark($im, 80, 80, 56);
imagettftext($im, 30, 0, 160, 120, hex($im, '#EEF1F7'), $bold, 'Atom Services');
imagettftext($im, 64, 0, 80, 290, hex($im, '#EEF1F7'), $bold, 'Shop now.');
imagettftext($im, 64, 0, 80, 380, hex($im, '#F5A524'), $bold, 'Pay in steps.');
imagettftext($im, 26, 0, 82, 450, hex($im, '#A9B4CB'), $semi, 'AI-powered product financing, built in Lahore.');
imagefilledrectangle($im, 80, 510, 668, 560, hex($im, '#F5A524', 100));
imagerectangle($im, 80, 510, 668, 560, hex($im, '#F5A524'));
imagettftext($im, 20, 0, 100, 544, hex($im, '#EEF1F7'), $mono, 'GITEX Global 2026 · Dubai · 8–11 Dec');
imagepng($im, "$root/assets/img/og-image.png", 9);
printf("og-image.png (%d KB)\n", filesize("$root/assets/img/og-image.png") / 1024);

// Apple touch icon, 180×180
$icon = imagecreatetruecolor(180, 180);
imageantialias($icon, true);
imagefilledrectangle($icon, 0, 0, 180, 180, hex($icon, '#14213D'));
place_mark($icon, 30, 30, 120);
imagepng($icon, "$root/assets/img/apple-touch-icon.png", 9);
printf("apple-touch-icon.png (%d KB)\n", filesize("$root/assets/img/apple-touch-icon.png") / 1024);
