<?php
/**
 * Simple Database Backup Script
 * Uses mysqldump command
 */

// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'findownn_admin';

// Backup directory
$backupDir = __DIR__ . '/storage/backups';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "Created backup directory: {$backupDir}<br>";
}

// Generate filename
$timestamp = date('Ymd_His');
$filename = "{$dbName}_{$timestamp}.sql";
$filepath = $backupDir . '/' . $filename;

echo "<h2>Database Backup</h2>";
echo "<p>Database: <strong>{$dbName}</strong></p>";
echo "<p>Backup file: <strong>{$filename}</strong></p>";
echo "<hr>";

// Try to find mysqldump
$possiblePaths = [
    'C:\\xampp\\mysql\\bin\\mysqldump.exe',
    'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
    'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
    'mysqldump.exe',
    'mysqldump'
];

$mysqldumpPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $mysqldumpPath = $path;
        echo "<p>✓ Found mysqldump: <code>{$path}</code></p>";
        break;
    }
}

if (!$mysqldumpPath) {
    // Try using 'where' command to find mysqldump in PATH
    exec('where mysqldump 2>nul', $whereOutput, $whereCode);
    if ($whereCode === 0 && !empty($whereOutput[0])) {
        $mysqldumpPath = trim($whereOutput[0]);
        echo "<p>✓ Found mysqldump in PATH: <code>{$mysqldumpPath}</code></p>";
    } else {
        $mysqldumpPath = 'mysqldump'; // Last resort - try anyway
        echo "<p>⚠️ mysqldump not found in expected locations, attempting anyway...</p>";
    }
}

// Build command - escape path properly for Windows
$command = "\"{$mysqldumpPath}\" --host={$dbHost} --user={$dbUser}";
if (!empty($dbPass)) {
    $command .= " --password=\"{$dbPass}\"";
}
$command .= " --result-file=\"{$filepath}\" {$dbName}";

echo "<p>Executing backup...</p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 3px;'>" . htmlspecialchars($command) . "</pre>";

// Execute
exec($command . " 2>&1", $output, $returnCode);

// Check result
if (file_exists($filepath)) {
    $fileSize = filesize($filepath);
    $fileSizeKB = round($fileSize / 1024, 2);
    
    // Check if file has content (should be at least 1KB for a real backup)
    if ($fileSize > 1000) {
        echo "<div style='margin: 20px 0; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<h3 style='color: #155724; margin: 0 0 10px 0;'>✓ Backup Successful!</h3>";
        echo "<p><strong>File:</strong> {$filename}</p>";
        echo "<p><strong>Size:</strong> {$fileSizeKB} KB</p>";
        echo "<p><strong>Location:</strong> {$filepath}</p>";
        echo "</div>";
        
        echo "<a href='/findownn_website/admin/storage/backups/{$filename}' download style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📥 Download Backup</a>";
        echo "<a href='/findownn_website/admin/storage/backups/' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>📁 View All Backups</a>";
    } else {
        echo "<div style='margin: 20px 0; padding: 20px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;'>";
        echo "<h3 style='color: #856404;'>⚠️ Backup File Too Small</h3>";
        echo "<p><strong>File size:</strong> {$fileSize} bytes (expected > 1000 bytes)</p>";
        echo "<p>The backup file was created but appears to be empty or contain only error messages.</p>";
        
        // Show file content if small
        if ($fileSize < 500) {
            $content = file_get_contents($filepath);
            echo "<p><strong>File content:</strong></p>";
            echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 3px; max-height: 200px; overflow: auto;'>" . htmlspecialchars($content) . "</pre>";
        }
        echo "</div>";
    }
} else {
    echo "<div style='margin: 20px 0; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24;'>❌ Backup Failed</h3>";
    echo "<p><strong>Return code:</strong> {$returnCode}</p>";
    echo "<p>The backup file was not created.</p>";
    
    if (!empty($output)) {
        echo "<p><strong>Command output:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 3px; max-height: 200px; overflow: auto;'>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
    }
    
    echo "<hr>";
    echo "<h4>Troubleshooting:</h4>";
    echo "<ol>";
    echo "<li>Verify XAMPP MySQL is running</li>";
    echo "<li>Check if mysqldump.exe exists at: <code>C:\\xampp\\mysql\\bin\\mysqldump.exe</code></li>";
    echo "<li>Try running the batch file: <code>admin/backup.bat</code></li>";
    echo "<li>Or use phpMyAdmin to export the database manually</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='/findownn_website/admin/dashboard'>← Back to Dashboard</a></p>";
