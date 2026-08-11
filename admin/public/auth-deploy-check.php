<?php
/**
 * Auth portal deploy checker — run once on Hostinger after upload.
 * URL: https://yoursite.com/admin/public/auth-deploy-check.php
 * Delete this file after all checks pass.
 */
header('Content-Type: text/html; charset=utf-8');

$adminRoot = dirname(__DIR__);
$siteRoot  = dirname($adminRoot);

$checks = [
    'admin/views/auth/login-owner.php'       => $adminRoot . '/views/auth/login-owner.php',
    'admin/views/auth/login-admin.php'       => $adminRoot . '/views/auth/login-admin.php',
    'admin/views/auth/register-owner.php'    => $adminRoot . '/views/auth/register-owner.php',
    'admin/views/auth/_auth-split-open.php'  => $adminRoot . '/views/auth/_auth-split-open.php',
    'admin/views/auth/_auth-split-close.php' => $adminRoot . '/views/auth/_auth-split-close.php',
    'admin/app/Controllers/AuthController.php' => $adminRoot . '/app/Controllers/AuthController.php',
    'admin/routes/web.php'                   => $adminRoot . '/routes/web.php',
    'admin/public/assets/css/admin.css'      => $adminRoot . '/public/assets/css/admin.css',
    'pages/dashboard.php'                    => $siteRoot . '/pages/dashboard.php',
    'includes/user-auth.php'                 => $siteRoot . '/includes/user-auth.php',
];

$missing = [];
$ok = [];

foreach ($checks as $label => $path) {
    if (is_file($path)) {
        $ok[] = $label;
    } else {
        $missing[] = $label;
    }
}

$stale = [];
$splitMarker = 'auth-split';
$splitFiles = [
    'register-owner.php (split layout)' => $adminRoot . '/views/auth/register-owner.php',
    'login-owner.php (split layout)'    => $adminRoot . '/views/auth/login-owner.php',
    'login-admin.php (split layout)'    => $adminRoot . '/views/auth/login-admin.php',
];
foreach ($splitFiles as $label => $path) {
    if (is_file($path) && strpos((string) file_get_contents($path), $splitMarker) === false) {
        $stale[] = $label;
    }
}

$cssPath = $adminRoot . '/public/assets/css/admin.css';
$cssBroken = is_file($cssPath) && str_contains((string) file_get_contents($cssPath), '.auth-card { display: none;');

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Findownn Auth Deploy Check</title>
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
  <h1>Findownn — Auth Deploy Check</h1>

  <?php if (empty($missing)): ?>
    <p class="pass"><strong>All required files are present.</strong></p>
    <?php if ($cssBroken): ?>
    <p class="fail"><strong>admin.css still hides auth forms — re-upload <code>admin/public/assets/css/admin.css</code> (v2.6+)</strong></p>
    <?php endif; ?>
    <?php if (!empty($stale)): ?>
    <p class="fail"><strong><?= count($stale) ?> auth view(s) are outdated — re-upload from local XAMPP:</strong></p>
    <div class="box"><ul><?php foreach ($stale as $s): ?><li><code><?= htmlspecialchars($s) ?></code></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <ul>
      <li><a href="../../owner/login">/admin/owner/login</a> — Venue owner</li>
      <li><a href="../../login">/admin/login</a> — Super admin</li>
      <li><a href="../../../dashboard">/dashboard</a> — Player dashboard</li>
    </ul>
    <p><small>Delete <code>admin/public/auth-deploy-check.php</code> when done.</small></p>
  <?php else: ?>
    <p class="fail"><strong><?= count($missing) ?> file(s) missing — upload from local XAMPP project:</strong></p>
    <div class="box"><ul><?php foreach ($missing as $m): ?><li><code><?= htmlspecialchars($m) ?></code></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <div class="box"><small>Found: <?= count($ok) ?> / <?= count($checks) ?></small></div>
</body>
</html>
