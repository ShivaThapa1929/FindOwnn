<?php
/**
 * Database Backup Script
 * Creates a full SQL dump of the database
 */

require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Config.php';

use App\Core\Database;
use App\Core\Config;

set_time_limit(300); // 5 minutes

$db = Database::getInstance();

// Get database credentials
$dbName = Config::get('db.database', 'findownn_admin');
$dbHost = Config::get('db.host', 'localhost');
$dbUser = Config::get('db.username', 'root');
$dbPass = Config::get('db.password', '');

// Create backup directory if it doesn't exist
$backupDir = __DIR__ . '/storage/backups';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Generate filename with timestamp
$timestamp = date('Ymd_His');
$filename = "{$dbName}_backup_{$timestamp}.sql";
$filepath = $backupDir . '/' . $filename;

echo "<h2>Database Backup</h2>";
echo "<p>Starting backup of database: <strong>{$dbName}</strong></p>";

try {
    // Start output buffering for SQL dump
    $sqlDump = "-- ============================================================\n";
    $sqlDump .= "-- Database Backup: {$dbName}\n";
    $sqlDump .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
    $sqlDump .= "-- ============================================================\n\n";
    $sqlDump .= "SET FOREIGN_KEY_CHECKS = 0;\n";
    $sqlDump .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $sqlDump .= "SET time_zone = \"+00:00\";\n\n";

    // Get all tables
    $tables = $db->fetchAll("SHOW TABLES");
    $tableColumn = "Tables_in_{$dbName}";
    
    echo "<p>Found " . count($tables) . " tables</p>";
    echo "<ul>";

    foreach ($tables as $table) {
        $tableName = $table[$tableColumn];
        echo "<li>Backing up: <strong>{$tableName}</strong>... ";
        
        // Get CREATE TABLE statement
        $createTable = $db->fetch("SHOW CREATE TABLE `{$tableName}`");
        $sqlDump .= "\n-- --------------------------------------------------------\n";
        $sqlDump .= "-- Table structure for `{$tableName}`\n";
        $sqlDump .= "-- --------------------------------------------------------\n\n";
        $sqlDump .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
        $sqlDump .= $createTable['Create Table'] . ";\n\n";

        // Get table data
        $rows = $db->fetchAll("SELECT * FROM `{$tableName}`");
        $rowCount = count($rows);
        
        if ($rowCount > 0) {
            $sqlDump .= "-- Dumping data for table `{$tableName}`\n";
            $sqlDump .= "-- {$rowCount} rows\n\n";
            
            // Get column names
            $columns = $db->fetchAll("SHOW COLUMNS FROM `{$tableName}`");
            $columnNames = array_map(fn($col) => "`{$col['Field']}`", $columns);
            $columnList = implode(', ', $columnNames);
            
            // Insert data in batches
            $batchSize = 100;
            $batches = array_chunk($rows, $batchSize);
            
            foreach ($batches as $batch) {
                $sqlDump .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES\n";
                $values = [];
                
                foreach ($batch as $row) {
                    $escapedValues = array_map(function($value) use ($db) {
                        if ($value === null) {
                            return 'NULL';
                        }
                        return "'" . addslashes($value) . "'";
                    }, array_values($row));
                    $values[] = "(" . implode(', ', $escapedValues) . ")";
                }
                
                $sqlDump .= implode(",\n", $values) . ";\n\n";
            }
        } else {
            $sqlDump .= "-- No data for table `{$tableName}`\n\n";
        }
        
        echo "<span style='color: green;'>✓ {$rowCount} rows</span></li>\n";
    }
    
    echo "</ul>";

    $sqlDump .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    $sqlDump .= "\n-- Backup completed: " . date('Y-m-d H:i:s') . "\n";

    // Write to file
    $bytesWritten = file_put_contents($filepath, $sqlDump);
    
    if ($bytesWritten === false) {
        throw new Exception("Failed to write backup file");
    }

    $fileSize = round($bytesWritten / 1024, 2);
    
    echo "<div style='margin: 20px 0; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h3 style='color: #155724; margin: 0 0 10px 0;'>✓ Backup Completed Successfully!</h3>";
    echo "<p style='margin: 5px 0;'><strong>File:</strong> {$filename}</p>";
    echo "<p style='margin: 5px 0;'><strong>Size:</strong> {$fileSize} KB</p>";
    echo "<p style='margin: 5px 0;'><strong>Location:</strong> storage/backups/</p>";
    echo "<p style='margin: 5px 0;'><strong>Tables:</strong> " . count($tables) . "</p>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='/findownn_website/admin/storage/backups/{$filename}' download style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📥 Download Backup</a>";
    echo "<a href='/findownn_website/admin/dashboard' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>← Back to Dashboard</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='margin: 20px 0; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24; margin: 0 0 10px 0;'>❌ Backup Failed</h3>";
    echo "<p style='margin: 5px 0;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px; overflow: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Script execution time: " . round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2) . " seconds</small></p>";
