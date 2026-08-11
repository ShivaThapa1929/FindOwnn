<?php
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Config.php';

use App\Core\Database;
use App\Core\Config;

echo "Running API Token Migration...\n\n";

try {
    $db = Database::getInstance();
    
    // Read migration file
    $migrationFile = __DIR__ . '/database/migrations/004_add_api_token_to_users.sql';
    $sql = file_get_contents($migrationFile);
    
    // Split by semicolons to execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $db->execute($statement);
            echo "✓ Executed statement\n";
        }
    }
    
    echo "\n✅ API Token migration completed successfully!\n\n";
    echo "API token column added to users table.\n";
    echo "Tokens will be generated on user login.\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
