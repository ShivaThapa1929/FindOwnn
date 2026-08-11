<?php
// Check and create image tables if they don't exist

require_once __DIR__ . '/app/Core/Config.php';
require_once __DIR__ . '/app/Core/Logger.php';
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;
use App\Core\Config;

try {
    // Initialize config
    Config::load(__DIR__ . '/.env');
    
    $db = Database::getInstance();
    
    echo "Checking image tables...\n\n";
    
    // Check venue_images
    $result = $db->query("SHOW TABLES LIKE 'venue_images'");
    if ($result->rowCount() == 0) {
        echo "❌ venue_images table NOT FOUND\n";
        echo "Creating venue_images table...\n";
        
        $db->execute("
            CREATE TABLE venue_images (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                venue_id INT UNSIGNED NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                image_type ENUM('featured', 'gallery') DEFAULT 'gallery',
                caption VARCHAR(255) DEFAULT NULL,
                sort_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE,
                INDEX idx_venue_id (venue_id),
                INDEX idx_image_type (image_type),
                INDEX idx_sort_order (sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "✅ venue_images table created\n\n";
    } else {
        echo "✅ venue_images table exists\n";
        
        // Check columns
        $columns = $db->fetchAll("SHOW COLUMNS FROM venue_images");
        $columnNames = array_column($columns, 'Field');
        echo "Columns: " . implode(', ', $columnNames) . "\n\n";
    }
    
    // Check court_images
    $result = $db->query("SHOW TABLES LIKE 'court_images'");
    if ($result->rowCount() == 0) {
        echo "❌ court_images table NOT FOUND\n";
        echo "Creating court_images table...\n";
        
        $db->execute("
            CREATE TABLE court_images (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                court_id INT UNSIGNED NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                image_type ENUM('featured', 'gallery') DEFAULT 'gallery',
                caption VARCHAR(255) DEFAULT NULL,
                sort_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FOREIGN KEY (court_id) REFERENCES courts(id) ON DELETE CASCADE,
                INDEX idx_court_id (court_id),
                INDEX idx_image_type (image_type),
                INDEX idx_sort_order (sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "✅ court_images table created\n\n";
    } else {
        echo "✅ court_images table exists\n";
        
        // Check columns
        $columns = $db->fetchAll("SHOW COLUMNS FROM court_images");
        $columnNames = array_column($columns, 'Field');
        echo "Columns: " . implode(', ', $columnNames) . "\n\n";
    }
    
    echo "✅ All checks complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
