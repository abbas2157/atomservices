<?php
/**
 * XML sitemap, served as /sitemap.xml (.htaccess rewrite). Generated from SEO_PAGES so a page
 * hidden in /admin/seo ("Hide this page from Google") is also dropped from the sitemap.
 * <lastmod> is the page file's modification time.
 */

require_once __DIR__ . '/includes/seo_meta.php';

header('Content-Type: application/xml; charset=utf-8');

$extras = [
  '/' => ['weekly', '1.0'],
  '/services' => ['monthly', '0.8'],
  '/about' => ['monthly', '0.7'],
  '/contact' => ['monthly', '0.7'],
  '/faqs' => ['monthly', '0.6'],
  '/privacy-policy' => ['yearly', '0.3'],
  '/terms' => ['yearly', '0.3'],
];

$hidden = [];
foreach (seo_all_meta() as $path => $row) {
  if (!empty($row['noindex'])) {
    $hidden[$path] = true;
  }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach (SEO_PAGES as $path => [$file, $label]) {
  if (isset($hidden[$path])) {
    continue;
  }
  [$changefreq, $priority] = $extras[$path] ?? ['monthly', '0.5'];
  $loc = $path === '/' ? absolute_url('') : absolute_url(ltrim($path, '/'));
  $lastmod = date('Y-m-d', (int) filemtime(__DIR__ . '/' . $file));
  echo "  <url>\n";
  echo '    <loc>' . e($loc) . "</loc>\n";
  echo '    <lastmod>' . $lastmod . "</lastmod>\n";
  echo '    <changefreq>' . $changefreq . "</changefreq>\n";
  echo '    <priority>' . $priority . "</priority>\n";
  echo "  </url>\n";
}
echo "</urlset>\n";
