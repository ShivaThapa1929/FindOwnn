<?php
/**
 * Migration Runner — Sports Table Schema Update
 * Can be executed via CLI: php run-sports-migration.php
 * Or via browser: https://yourdomain.com/admin/run-sports-migration.php
 */

header('Content-Type: text/plain; charset=utf-8');

// Path configurations
define('ROOT_PATH', __DIR__);

// Autoload loader
spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\'      => ROOT_PATH . '/app/',
        'Database\\' => ROOT_PATH . '/database/',
    ];
    foreach ($prefixes as $prefix => $base) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file     = $base . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) { require_once $file; return; }
        }
    }
});

// Load Helpers
if (file_exists(ROOT_PATH . '/app/Helpers/functions.php')) {
    require_once ROOT_PATH . '/app/Helpers/functions.php';
}

use App\Core\Config;
use App\Core\Database;

echo "🚀 Starting Sports Table Schema Migration...\n";
echo "=========================================\n\n";

try {
    // Load config
    Config::load(ROOT_PATH . '/.env');
    
    // Connect to database
    $db = Database::getInstance();
    echo "✓ Connected to database: " . Config::get('DB_DATABASE') . "\n";
    
    // SQL Alter queries
    $queries = [
        "ALTER TABLE `sports` ADD COLUMN IF NOT EXISTS `color` VARCHAR(20) NOT NULL DEFAULT '#22c55e' AFTER `icon`",
        "ALTER TABLE `sports` ADD COLUMN IF NOT EXISTS `is_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`"
    ];
    
    foreach ($queries as $query) {
        echo "▶ Executing: " . $query . "\n";
        $db->rawExec($query);
        echo "  ✓ Executed successfully.\n\n";
    }
    
    echo "✅ Migration completed successfully! The sports table is fully up to date.\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
}
