<?php
/**
 * Quick Migration Runner for Image Tables
 * Run this once: php run-image-migration.php
 */

require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Config.php';

use App\Core\Database;
use App\Core\Config;

echo "Running Image Tables Migration...\n\n";

try {
    $db = Database::getInstance();
    
    // Read and execute migration
    $sql = file_get_contents(__DIR__ . '/database/migrations/003_add_image_tables.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->execute($statement);
            echo "✓ Executed statement\n";
        }
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "\nImage tables created:\n";
    echo "  - venue_images\n";
    echo "  - court_images\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n\n";
    exit(1);
}
