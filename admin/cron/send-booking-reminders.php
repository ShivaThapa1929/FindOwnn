<?php
/**
 * Cron: Send automated booking reminders via WhatsApp
 *
 * Run every hour via Hostinger cron or Windows Task Scheduler:
 *   php c:\xampp\htdocs\findownn_website\admin\cron\send-booking-reminders.php
 *
 * Or via URL (protect in production):
 *   /admin/cron/send-booking-reminders.php?key=YOUR_CRON_SECRET
 */

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/app/Core/Config.php';
require_once ROOT_PATH . '/app/Core/Logger.php';
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Services/WhatsAppService.php';
require_once ROOT_PATH . '/app/Services/BookingReminderService.php';

use App\Core\Config;
use App\Core\Database;
use App\Services\BookingReminderService;

// Optional web trigger with secret key
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
    $service = new BookingReminderService();
    $result  = $service->sendUpcomingReminders();

    $output = [
        'success' => true,
        'sent'    => $result['sent'],
        'skipped' => $result['skipped'],
        'errors'  => $result['errors'],
        'time'    => date('c'),
    ];

    if (PHP_SAPI === 'cli') {
        echo "Booking reminders: {$result['sent']} sent, {$result['skipped']} skipped\n";
        if (!empty($result['errors'])) {
            echo "Errors:\n" . implode("\n", $result['errors']) . "\n";
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
