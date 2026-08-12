<?php
/**
 * Add OpenWA settings to whatsapp group
 */
define('ROOT_PATH', __DIR__);

require_once __DIR__ . '/app/Core/Config.php';
require_once __DIR__ . '/app/Core/Logger.php';
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;
use App\Core\Config;

try {
    Config::load(__DIR__ . '/.env');
    $db = Database::getInstance();

    echo "Adding OpenWA settings...\n\n";

    $settings = [
        ['whatsapp', 'openwa_base_url', '', 'text', 'OpenWA Base URL (e.g. http://localhost:2785)'],
        ['whatsapp', 'openwa_api_key', '', 'password', 'OpenWA API Key'],
        ['whatsapp', 'openwa_session_id', 'findownn', 'text', 'OpenWA Session ID'],
        ['whatsapp', 'openwa_webhook_secret', '', 'password', 'OpenWA Webhook HMAC Secret'],
        ['whatsapp', 'send_subscription_start', '1', 'boolean', 'WhatsApp on plan activation'],
        ['whatsapp', 'send_subscription_expiry_warning', '1', 'boolean', 'WhatsApp before plan expiry'],
        ['whatsapp', 'send_subscription_expired', '1', 'boolean', 'WhatsApp when plan expires'],
        ['whatsapp', 'subscription_warning_days', '7', 'number', 'Days before expiry to warn owners'],
    ];

    foreach ($settings as $s) {
        $db->execute(
            "INSERT INTO settings (`group`, `key`, value, type, label, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE label = VALUES(label), type = VALUES(type), updated_at = NOW()",
            $s
        );
        echo "  ✓ {$s[1]}\n";
    }

    // Update provider label to include openwa
    $db->execute(
        "UPDATE settings SET label = 'WhatsApp Provider (twilio/meta/openwa)' WHERE `key` = 'whatsapp_provider'"
    );

    $webhookSecret = bin2hex(random_bytes(16));

    $defaults = [
        'whatsapp_provider'         => 'openwa',
        'openwa_base_url'           => 'http://localhost:2785',
        'openwa_session_id'         => 'findownn',
        'openwa_webhook_secret'     => $webhookSecret,
        'send_booking_confirmation' => '1',
        'send_payment_confirmation' => '1',
        'send_reminder'             => '1',
        'reminder_hours_before'     => '24',
        'send_subscription_start'           => '1',
        'send_subscription_expiry_warning'  => '1',
        'send_subscription_expired'         => '1',
        'subscription_warning_days'         => '7',
    ];

    foreach ($defaults as $key => $value) {
        $db->execute(
            "UPDATE settings SET value = ?, updated_at = NOW() WHERE `key` = ?",
            [$value, $key]
        );
    }

    // CRON_SECRET in .env for reminder cron URL trigger
    $envPath = __DIR__ . '/.env';
    if (is_file($envPath)) {
        $env = file_get_contents($envPath);
        if (!str_contains($env, 'CRON_SECRET=')) {
            $cronSecret = bin2hex(random_bytes(16));
            file_put_contents($envPath, rtrim($env) . "\nCRON_SECRET={$cronSecret}\n");
            echo "  ✓ CRON_SECRET added to .env\n";
        }
    }

    echo "\n✅ OpenWA settings ready.\n";
    echo "   Admin panel: /admin/openwa\n";
    echo "   Webhook URL: http://localhost/findownn_website/api/v1/openwa/webhook\n";
    echo "   Next: install OpenWA server — see admin/OPENWA_SETUP.md\n";
    echo "   Then paste API key in Admin → OpenWA\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
