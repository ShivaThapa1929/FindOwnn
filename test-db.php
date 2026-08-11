<?php
// Test database queries to find the issue
try {
    $pdo = new PDO('mysql:host=localhost;dbname=findownn_admin', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully\n\n";
    
    // Test the problematic query from screenshot
    echo "Testing queries...\n\n";
    
    // Check if type column exists in any table with cs prefix
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('type', $columns)) {
            echo "Table '$table' has 'type' column\n";
            echo "Columns: " . implode(', ', $columns) . "\n\n";
        }
    }
    
    echo "\n\nAll done!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
