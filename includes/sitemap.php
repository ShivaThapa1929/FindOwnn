<?php
/**
 * Dynamic XML sitemap — public pages + active venues from DB.
 */

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base     = rtrim($protocol . '://' . $host . ($script === '/' ? '' : $script), '/');

$pages = [
    ['loc' => '/',        'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/venues',  'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/sports',  'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => '/partner', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/about',   'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => '/contact', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => '/privacy', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => '/terms',   'priority' => '0.3', 'changefreq' => 'yearly'],
];

$venuePages = [];

try {
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', __DIR__ . '/../admin');
    }
    require_once ROOT_PATH . '/app/Core/Config.php';
    \App\Core\Config::load(ROOT_PATH . '/.env');
    require_once ROOT_PATH . '/app/Core/Logger.php';
    require_once ROOT_PATH . '/app/Core/Database.php';

    $db = \App\Core\Database::getInstance();
    $rows = $db->fetchAll(
        "SELECT id, updated_at FROM venues
         WHERE status = 'active' AND deleted_at IS NULL
         ORDER BY id ASC"
    );

    foreach ($rows as $row) {
        $venuePages[] = [
            'loc'        => '/venue-details?id=' . (int) $row['id'],
            'priority'   => '0.7',
            'changefreq' => 'weekly',
            'lastmod'    => !empty($row['updated_at'])
                ? date('Y-m-d', strtotime($row['updated_at']))
                : date('Y-m-d'),
        ];
    }
} catch (Throwable $e) {
    // Static pages only if DB unavailable
}

$defaultLastmod = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $page): ?>
  <url>
    <loc><?= htmlspecialchars($base . $page['loc'], ENT_XML1) ?></loc>
    <lastmod><?= $defaultLastmod ?></lastmod>
    <changefreq><?= $page['changefreq'] ?></changefreq>
    <priority><?= $page['priority'] ?></priority>
  </url>
<?php endforeach; ?>
<?php foreach ($venuePages as $page): ?>
  <url>
    <loc><?= htmlspecialchars($base . $page['loc'], ENT_XML1) ?></loc>
    <lastmod><?= htmlspecialchars($page['lastmod'], ENT_XML1) ?></lastmod>
    <changefreq><?= $page['changefreq'] ?></changefreq>
    <priority><?= $page['priority'] ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
