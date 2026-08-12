<?php
/**
 * Cron: subscription expiry warnings + optional manual expire run
 *
 * CLI: php admin/cron/send-subscription-notifications.php
 * Web: /admin/cron/send-subscription-notifications.php?key=CRON_SECRET
 *      &expire=1  — also run expireOld (sends expired notifications)
 */
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/app/Core/Config.php';
require_once ROOT_PATH . '/app/Core/Logger.php';
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Services/SubscriptionNotificationService.php';
require_once ROOT_PATH . '/app/Models/Subscription.php';

use App\Core\Config;
use App\Services\SubscriptionNotificationService;
use App\Models\Subscription;

if (PHP_SAPI !== 'cli') {
    Config::load(ROOT_PATH . '/.env');
    $secret = Config::get('CRON_SECRET', '');
    $key    = $_GET['key'] ?? '';

    if ($secret === '' || !hash_equals($secret, $key)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }

    header('Content-Type: application/json');
} else {
    Config::load(ROOT_PATH . '/.env');
}

try {
    $service = new SubscriptionNotificationService();
    $warnings = $service->processExpiryWarnings();

    $expired = ['expired' => 0, 'notifications' => []];
    $runExpire = PHP_SAPI === 'cli'
        ? in_array('--expire', $argv ?? [], true)
        : !empty($_GET['expire']);

    if ($runExpire) {
        $expired = $service->expireAndNotify();
    }

    $output = [
        'success'          => true,
        'warnings_sent'    => $warnings['sent'],
        'warnings_skipped' => $warnings['skipped'],
        'warning_errors'   => $warnings['errors'],
        'expired_count'    => $expired['expired'] ?? 0,
        'expired_notified' => count($expired['notifications'] ?? []),
        'whatsapp_ready'   => $service->isWhatsAppConfigured(),
        'time'             => date('c'),
    ];

    if (PHP_SAPI === 'cli') {
        echo "Subscription notifications\n";
        echo "  Warnings sent: {$warnings['sent']}, skipped: {$warnings['skipped']}\n";
        if ($runExpire) {
            echo "  Expired: {$output['expired_count']}, notified: {$output['expired_notified']}\n";
        }
        if (!empty($warnings['errors'])) {
            echo "  Errors: " . implode('; ', $warnings['errors']) . "\n";
        }
    } else {
        echo json_encode($output, JSON_PRETTY_PRINT);
    }
} catch (Throwable $e) {
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
