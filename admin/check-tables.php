<?php
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Config.php';

use App\Core\Database;

$db = Database::getInstance();

// Check if image tables exist
$tables = $db->fetchAll("SHOW TABLES LIKE '%images'");

echo "Image Tables Found:\n";
foreach ($tables as $table) {
    echo "  - " . implode(', ', $table) . "\n";
}

// Check if venue_images table exists
$venueImagesExists = $db->fetch("SHOW TABLES LIKE 'venue_images'");
echo "\nvenue_images table: " . ($venueImagesExists ? "EXISTS" : "NOT FOUND") . "\n";

// Check if court_images table exists  
$courtImagesExists = $db->fetch("SHOW TABLES LIKE 'court_images'");
echo "court_images table: " . ($courtImagesExists ? "EXISTS" : "NOT FOUND") . "\n";

// If they don't exist, show the migration file
if (!$venueImagesExists || !$courtImagesExists) {
    echo "\n⚠️  Image tables are missing! Run the migration:\n";
    echo "C:\\xampp\\php\\php.exe run-image-migration.php\n";
}
