<?php
/**
 * Migration: add reminder_sent_at to bookings + fix walk-in player roles
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

    echo "Players & reminders migration...\n\n";

    try {
        $db->execute(
            "ALTER TABLE bookings ADD COLUMN reminder_sent_at DATETIME NULL DEFAULT NULL AFTER cancelled_at"
        );
        echo "✅ Added reminder_sent_at to bookings\n";
    } catch (Exception $e) {
        if (str_contains($e->getMessage(), 'Duplicate column')) {
            echo "⚠️ reminder_sent_at already exists\n";
        } else {
            throw $e;
        }
    }

    $fixed = $db->execute(
        "UPDATE users SET role = 'player', status = 'active'
         WHERE email LIKE '%@offline.findownn' AND role != 'player'"
    );
    echo "✅ Fixed walk-in user roles ({$fixed} rows)\n";

    echo "\nDone.\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
