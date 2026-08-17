<?php
/**
 * Dynamic XML sitemap for public pages.
 */

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base     = rtrim($protocol . '://' . $host . ($script === '/' ? '' : $script), '/');

$pages = [
    ['loc' => '/',           'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/venues',     'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/sports',     'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => '/partner',    'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/about',      'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => '/contact',    'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => '/privacy',    'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => '/terms',      'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => '/login',      'priority' => '0.4', 'changefreq' => 'monthly'],
    ['loc' => '/register',   'priority' => '0.4', 'changefreq' => 'monthly'],
];

$lastmod = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $page): ?>
  <url>
    <loc><?= htmlspecialchars($base . $page['loc'], ENT_XML1) ?></loc>
    <lastmod><?= $lastmod ?></lastmod>
    <changefreq><?= $page['changefreq'] ?></changefreq>
    <priority><?= $page['priority'] ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
