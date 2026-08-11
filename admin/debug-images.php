<?php
// Debug image paths

require_once __DIR__ . '/app/Core/Config.php';
require_once __DIR__ . '/app/Core/Logger.php';
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;
use App\Core\Config;

try {
    Config::load(__DIR__ . '/.env');
    $db = Database::getInstance();
    
    echo "=== VENUE IMAGES DEBUG ===\n\n";
    
    $images = $db->fetchAll("SELECT * FROM venue_images ORDER BY id DESC LIMIT 5");
    
    foreach ($images as $img) {
        echo "Image ID: {$img['id']}\n";
        echo "Venue ID: {$img['venue_id']}\n";
        echo "Image Path (DB): {$img['image_path']}\n";
        
        $fullPath = __DIR__ . '/public/uploads/' . $img['image_path'];
        echo "Full File Path: {$fullPath}\n";
        echo "File Exists: " . (file_exists($fullPath) ? "YES" : "NO") . "\n";
        
        if (file_exists($fullPath)) {
            echo "File Size: " . filesize($fullPath) . " bytes\n";
        }
        
        echo "Expected URL: http://localhost:8080/findownn_website/admin/public/uploads/{$img['image_path']}\n";
        echo "\n" . str_repeat('-', 80) . "\n\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
