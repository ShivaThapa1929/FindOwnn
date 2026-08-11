<?php
/**
 * Add missing 'type' column to venues table
 */

$host = 'localhost';
$db   = 'findownn_admin';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database: $db\n\n";
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM venues LIKE 'type'");
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "✓ Column 'type' already exists in venues table\n";
    } else {
        echo "Adding 'type' column to venues table...\n";
        
        // Add type column after name
        $pdo->exec("
            ALTER TABLE venues 
            ADD COLUMN type ENUM('indoor', 'outdoor', 'hybrid') DEFAULT 'outdoor' 
            AFTER name
        ");
        
        echo "✓ Successfully added 'type' column to venues table\n";
    }
    
    // Update existing venues to have a default type based on their characteristics
    $updated = $pdo->exec("
        UPDATE venues 
        SET type = 'outdoor' 
        WHERE type IS NULL
    ");
    
    echo "\n✓ Updated $updated existing venues with default type\n";
    
    echo "\n✅ All done! Venues table now has 'type' column.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
