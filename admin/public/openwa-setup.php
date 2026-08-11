<?php
/**
 * OpenWA DB setup — run once on Hostinger after upload.
 * URL: /admin/public/openwa-setup.php?key=YOUR_CRON_SECRET
 * Delete this file after setup succeeds.
 */
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

header('Content-Type: text/html; charset=utf-8');

require_once ROOT_PATH . '/app/Core/Config.php';
require_once ROOT_PATH . '/app/Core/Logger.php';
require_once ROOT_PATH . '/app/Core/Database.php';

require_once ROOT_PATH . '/app/Helpers/functions.php';

use App\Core\Config;
use App\Core\Database;

$log = [];
$baseRow = false;
$keyRow  = false;
$ok      = false;

try {
    Config::load(ROOT_PATH . '/.env');
    $secret = Config::get('CRON_SECRET', '');
    if ($secret === '' || ($_GET['key'] ?? '') !== $secret) {
        http_response_code(403);
        echo '<h1>403 Forbidden</h1><p>Add <code>?key=CRON_SECRET</code> from admin/.env</p>';
        exit;
    }

    $db = Database::getInstance();

    $settings = [
        ['whatsapp', 'openwa_base_url', '', 'text', 'OpenWA Base URL (e.g. https://your-openwa.onrender.com)'],
        ['whatsapp', 'openwa_api_key', '', 'password', 'OpenWA API Key'],
        ['whatsapp', 'openwa_session_id', 'findownn', 'text', 'OpenWA Session ID'],
        ['whatsapp', 'openwa_webhook_secret', '', 'password', 'OpenWA Webhook HMAC Secret'],
    ];

    foreach ($settings as $s) {
        $db->execute(
            "INSERT INTO settings (`group`, `key`, value, type, label, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE label = VALUES(label), type = VALUES(type), updated_at = NOW()",
            $s
        );
        $log[] = "✓ Setting row: {$s[1]}";
    }

    $db->execute(
        "UPDATE settings SET label = 'WhatsApp Provider (twilio/meta/openwa)' WHERE `key` = 'whatsapp_provider'"
    );

    $existingSecret = $db->fetch("SELECT value FROM settings WHERE `key` = 'openwa_webhook_secret'");
    $webhookSecret  = (is_array($existingSecret) && ($existingSecret['value'] ?? '') !== '')
        ? $existingSecret['value']
        : bin2hex(random_bytes(16));

    $defaults = [
        'whatsapp_provider'           => 'openwa',
        'openwa_session_id'           => 'findownn',
        'openwa_webhook_secret'       => $webhookSecret,
        'send_booking_confirmation'   => '1',
        'send_payment_confirmation'     => '1',
        'send_reminder'               => '1',
        'reminder_hours_before'       => '24',
    ];

    foreach ($defaults as $key => $value) {
        $db->execute(
            "UPDATE settings SET value = ?, updated_at = NOW() WHERE `key` = ?",
            [$value, $key]
        );
        $log[] = "✓ Default: {$key}";
    }

    $baseRow = $db->fetch("SELECT value FROM settings WHERE `key` = 'openwa_base_url'");
    $keyRow  = $db->fetch("SELECT value FROM settings WHERE `key` = 'openwa_api_key'");

    $ok = true;
} catch (Throwable $e) {
    $ok  = false;
    $log[] = '❌ ' . $e->getMessage();
}

$webhookUrl = openwa_webhook_url();

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OpenWA Setup</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #0a0f0c; color: #e2e8f0; padding: 24px; max-width: 720px; margin: 0 auto; }
    h1 { color: #22c55e; font-size: 1.25rem; }
    .pass { color: #4ade80; }
    .fail { color: #f87171; }
    code { background: #1a2e1f; padding: 2px 6px; border-radius: 4px; }
    .box { background: #111916; border: 1px solid #1f3d28; border-radius: 12px; padding: 16px 20px; margin: 16px 0; }
    ol { line-height: 1.8; }
    a { color: #4ade80; }
  </style>
</head>
<body>
  <h1>Findownn — OpenWA DB Setup</h1>

  <?php if ($ok): ?>
    <p class="pass"><strong>Database settings ready.</strong></p>
    <div class="box"><ul><?php foreach ($log as $line): ?><li><?= htmlspecialchars($line) ?></li><?php endforeach; ?></ul></div>

    <?php if (!is_array($baseRow) || !is_array($keyRow) || ($baseRow['value'] ?? '') === '' || ($keyRow['value'] ?? '') === ''): ?>
    <div class="box">
      <strong>Next — OpenWA server + admin config:</strong>
      <ol>
        <li>Deploy OpenWA on <a href="https://dashboard.render.com/" target="_blank" rel="noopener">Render</a> using <code>admin/deploy/openwa/render.yaml</code></li>
        <li>Copy public URL + API key from Render logs</li>
        <li><a href="../openwa">Admin → OpenWA</a> → paste Base URL + API Key → <strong>Save</strong></li>
        <li><strong>Test Connection</strong> → <strong>Register Webhook</strong></li>
        <li>Open Web Dashboard → scan WhatsApp QR → <strong>Send Test</strong></li>
      </ol>
      <p class="small">Webhook URL: <code><?= htmlspecialchars($webhookUrl) ?></code></p>
    </div>
    <?php else: ?>
    <p><a href="../openwa">Open Admin → OpenWA</a></p>
    <?php endif; ?>

    <p><small>Delete <code>admin/public/openwa-setup.php</code> when done.</small></p>
  <?php else: ?>
    <p class="fail"><strong>Setup failed.</strong></p>
    <div class="box"><ul><?php foreach ($log as $line): ?><li><?= htmlspecialchars($line) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
</body>
</html>
