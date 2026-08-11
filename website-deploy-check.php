<?php
/**
 * Website deploy checker — run once on Hostinger after upload.
 * URL: https://yoursite.com/website-deploy-check.php
 * Delete this file after all checks pass.
 */
header('Content-Type: text/html; charset=utf-8');

$root = __DIR__;

$checks = [
    'pages/home.php'                    => $root . '/pages/home.php',
    'includes/app-screen-home.php'      => $root . '/includes/app-screen-home.php',
    'includes/app-screen-bookings.php'  => $root . '/includes/app-screen-bookings.php',
    'includes/header.php'               => $root . '/includes/header.php',
    'includes/footer.php'               => $root . '/includes/footer.php',
    'css/style.css'                     => $root . '/css/style.css',
    'css/responsive.css'                => $root . '/css/responsive.css',
    'css/home-enhancements.css'         => $root . '/css/home-enhancements.css',
    'js/script.js'                      => $root . '/js/script.js',
    'js/home-api.js'                    => $root . '/js/home-api.js',
    'assets/images/venue-cricket.jpg'   => $root . '/assets/images/venue-cricket.jpg',
    'assets/images/venue-pickleball.jpg' => $root . '/assets/images/venue-pickleball.jpg',
    'assets/images/venue-football.jpg'  => $root . '/assets/images/venue-football.jpg',
    'assets/images/app-home-screen.png' => $root . '/assets/images/app-home-screen.png',
];

$missing = [];
$ok = [];
$stale = [];

foreach ($checks as $label => $path) {
    if (is_file($path)) {
        $ok[] = $label;
    } else {
        $missing[] = $label;
    }
}

$homePath = $root . '/pages/home.php';
if (is_file($homePath)) {
    $home = (string) file_get_contents($homePath);
    if (strpos($home, 'app-screen-home.php') === false) {
        $stale[] = 'home.php — still using old mockup (missing app-screen-home include)';
    }
    if (strpos($home, 'app-screen-bookings.php') === false) {
        $stale[] = 'home.php — still using JS carousel (missing app-screen-bookings include)';
    }
    if (strpos($home, 'app-mockup.png') !== false) {
        $stale[] = 'home.php — still references app-mockup.png';
    }
}

$headerPath = $root . '/includes/header.php';
if (is_file($headerPath) && strpos((string) file_get_contents($headerPath), 'responsive.css') === false) {
    $stale[] = 'header.php — missing responsive.css link';
}

$scriptPath = $root . '/js/script.js';
if (is_file($scriptPath) && str_contains((string) file_get_contents($scriptPath), 'phone-screen-wrapper')) {
    $stale[] = 'script.js — still has fake phone carousel';
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Findownn Website Deploy Check</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #0a0f0c; color: #e2e8f0; padding: 24px; max-width: 720px; margin: 0 auto; }
    h1 { color: #22c55e; font-size: 1.25rem; }
    .pass { color: #4ade80; }
    .fail { color: #f87171; }
    ul { line-height: 1.8; }
    code { background: #1a2e1f; padding: 2px 6px; border-radius: 4px; font-size: 0.85rem; }
    .box { background: #111916; border: 1px solid #1f3d28; border-radius: 12px; padding: 16px 20px; margin: 16px 0; }
    a { color: #4ade80; }
  </style>
</head>
<body>
  <h1>Findownn — Website Deploy Check</h1>

  <?php if (empty($missing) && empty($stale)): ?>
    <p class="pass"><strong>All website files are present and up to date.</strong></p>
    <ul>
      <li><a href="./">Homepage</a> — real FindOwnn app mockups</li>
      <li><a href="sports">Sports</a> — mobile responsive</li>
      <li><a href="contact">Contact</a></li>
    </ul>
    <p><small>Hard refresh (Ctrl+Shift+R) after upload. Delete <code>website-deploy-check.php</code> when done.</small></p>
  <?php else: ?>
    <?php if (!empty($missing)): ?>
    <p class="fail"><strong><?= count($missing) ?> file(s) missing — upload from local XAMPP:</strong></p>
    <div class="box"><ul><?php foreach ($missing as $m): ?><li><code><?= htmlspecialchars($m) ?></code></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if (!empty($stale)): ?>
    <p class="fail"><strong><?= count($stale) ?> file(s) outdated:</strong></p>
    <div class="box"><ul><?php foreach ($stale as $s): ?><li><code><?= htmlspecialchars($s) ?></code></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="box"><small>Found: <?= count($ok) ?> / <?= count($checks) ?></small></div>
</body>
</html>
