<?php
/**
 * Pure PHP Database Backup Script
 * Does not require mysqldump - uses PHP to generate SQL
 */

// Load environment
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Database configuration
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$dbName = getenv('DB_DATABASE') ?: 'findownn_admin';

// Backup directory
$backupDir = __DIR__ . '/storage/backups';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Generate filename
$timestamp = date('Ymd_His');
$filename = "{$dbName}_{$timestamp}.sql";
$filepath = $backupDir . '/' . $filename;

// HTML Header
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Backup - Pure PHP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-top: 0; }
        .success { padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724; margin: 20px 0; }
        .error { padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24; margin: 20px 0; }
        .info { padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; color: #0c5460; margin: 20px 0; }
        .progress { padding: 10px; background: #e7f3ff; border-left: 4px solid #2196F3; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px; }
        .btn-success { background: #28a745; color: white; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h2>📦 Database Backup (Pure PHP)</h2>
    <div class="info">
        <strong>Database:</strong> <?php echo htmlspecialchars($dbName); ?><br>
        <strong>Host:</strong> <?php echo htmlspecialchars($dbHost); ?><br>
        <strong>Backup file:</strong> <?php echo htmlspecialchars($filename); ?>
    </div>

<?php

try {
    // Connect to database
    echo "<div class='progress'>Connecting to database...</div>";
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Start output buffer
    $sql = "-- =============================================\n";
    $sql .= "-- Database Backup: {$dbName}\n";
    $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- =============================================\n\n";
    $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $sql .= "SET time_zone = \"+00:00\";\n\n";
    
    // Get all tables
    echo "<div class='progress'>Fetching table list...</div>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='progress'>Found " . count($tables) . " tables to backup</div>";
    
    foreach ($tables as $table) {
        echo "<div class='progress'>Backing up table: <strong>{$table}</strong>...</div>";
        
        // Table structure
        $sql .= "-- =============================================\n";
        $sql .= "-- Table structure for `{$table}`\n";
        $sql .= "-- =============================================\n\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n\n";
        
        $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        $sql .= $createTable['Create Table'] . ";\n\n";
        
        // Table data
        $sql .= "-- =============================================\n";
        $sql .= "-- Data for table `{$table}`\n";
        $sql .= "-- =============================================\n\n";
        
        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $columnList = '`' . implode('`, `', $columns) . '`';
            
            $valueGroups = [];
            foreach ($rows as $row) {
                $values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = $pdo->quote($value);
                    }
                }
                $valueGroups[] = '(' . implode(', ', $values) . ')';
            }
            
            // Split into chunks of 100 rows
            $chunks = array_chunk($valueGroups, 100);
            foreach ($chunks as $chunk) {
                $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n";
                $sql .= implode(",\n", $chunk) . ";\n\n";
            }
            
            echo "<div class='progress'>✓ Backed up " . count($rows) . " rows from {$table}</div>";
        } else {
            echo "<div class='progress'>✓ Table {$table} is empty</div>";
        }
        
        $sql .= "\n";
    }
    
    // Write to file
    echo "<div class='progress'>Writing backup to file...</div>";
    $bytesWritten = file_put_contents($filepath, $sql);
    
    if ($bytesWritten === false) {
        throw new Exception("Failed to write backup file");
    }
    
    $fileSize = filesize($filepath);
    $fileSizeKB = round($fileSize / 1024, 2);
    $fileSizeMB = round($fileSize / (1024 * 1024), 2);
    
    echo "<div class='success'>";
    echo "<h3>✓ Backup Completed Successfully!</h3>";
    echo "<p><strong>File:</strong> {$filename}</p>";
    echo "<p><strong>Size:</strong> {$fileSizeMB} MB ({$fileSizeKB} KB)</p>";
    echo "<p><strong>Tables:</strong> " . count($tables) . "</p>";
    echo "<p><strong>Location:</strong> {$filepath}</p>";
    echo "</div>";
    
    echo "<a href='/findownn_website/admin/storage/backups/{$filename}' download class='btn btn-success'>📥 Download Backup</a>";
    echo "<a href='/findownn_website/admin/storage/backups/' class='btn btn-primary'>📁 View All Backups</a>";
    echo "<a href='/findownn_website/admin/dashboard' class='btn btn-secondary'>← Back to Dashboard</a>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Backup Failed</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

?>

</div>
</body>
</html>
