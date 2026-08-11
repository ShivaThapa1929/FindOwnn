<?php
// Add updated_at column to image tables

require_once __DIR__ . '/app/Core/Config.php';
require_once __DIR__ . '/app/Core/Logger.php';
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;
use App\Core\Config;

try {
    Config::load(__DIR__ . '/.env');
    $db = Database::getInstance();
    
    echo "Adding updated_at column to image tables...\n\n";
    
    // Add to venue_images
    try {
        $db->execute("
            ALTER TABLE venue_images 
            ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP 
            AFTER created_at
        ");
        echo "✅ Added updated_at to venue_images\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "⚠️ updated_at already exists in venue_images\n";
        } else {
            throw $e;
        }
    }
    
    // Add to court_images
    try {
        $db->execute("
            ALTER TABLE court_images 
            ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP 
            AFTER created_at
        ");
        echo "✅ Added updated_at to court_images\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "⚠️ updated_at already exists in court_images\n";
        } else {
            throw $e;
        }
    }
    
    echo "\n✅ Migration complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
