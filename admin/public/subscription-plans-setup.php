<?php
/**
 * Subscription plans DB setup — run once on Hostinger after upload.
 * URL: /admin/public/subscription-plans-setup.php?key=YOUR_CRON_SECRET
 * Delete after success.
 */
declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

header('Content-Type: text/html; charset=utf-8');

require_once ROOT_PATH . '/app/Core/Config.php';

use App\Core\Config;

$secret = '';
try {
    Config::load(ROOT_PATH . '/.env');
    $secret = Config::get('CRON_SECRET', '');
} catch (Throwable) {
    // fall through
}

if ($secret === '' || ($_GET['key'] ?? '') !== $secret) {
    http_response_code(403);
    echo '<h1>403 Forbidden</h1><p>Add <code>?key=CRON_SECRET</code> from admin/.env</p>';
    exit;
}

ob_start();
$ok = true;
try {
    require ROOT_PATH . '/setup-subscription-plans.php';
} catch (Throwable $e) {
    $ok = false;
    echo '❌ ' . $e->getMessage();
}
$output = ob_get_clean();

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscription Plans Setup</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #0a0f0c; color: #e2e8f0; padding: 24px; max-width: 720px; margin: 0 auto; }
    h1 { color: #22c55e; font-size: 1.25rem; }
    pre { background: #111916; border: 1px solid #1f3d28; border-radius: 12px; padding: 16px; white-space: pre-wrap; font-size: 0.85rem; }
    a { color: #4ade80; }
  </style>
</head>
<body>
  <h1>Findownn — Subscription Plans Setup</h1>
  <?php if ($ok): ?>
    <p><strong class="text-success">Plans updated successfully.</strong></p>
    <pre><?= htmlspecialchars($output) ?></pre>
    <p><a href="../subscriptions/my-plans">Owner plans page</a> · <a href="../subscriptions/plans">Admin plans</a></p>
    <p><small>Delete <code>admin/public/subscription-plans-setup.php</code> when done.</small></p>
  <?php else: ?>
    <p><strong style="color:#f87171">Setup failed.</strong></p>
    <pre><?= htmlspecialchars($output) ?></pre>
  <?php endif; ?>
</body>
</html>
