<?php
// Quick test script for image upload functionality
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Config.php';
require_once __DIR__ . '/app/Core/Session.php';

use App\Core\Database;
use App\Core\Session;

Session::start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    die("Please login first: <a href='/admin/login'>Login</a>");
}

echo "<!DOCTYPE html><html><head><title>Upload Test</title></head><body>";
echo "<h1>Image Upload Diagnostics</h1>";

// 1. Check directories
echo "<h2>1. Directory Check</h2>";
$dirs = [
    'uploads' => __DIR__ . '/public/uploads',
    'venues' => __DIR__ . '/public/uploads/venues',
    'courts' => __DIR__ . '/public/uploads/courts'
];

foreach ($dirs as $name => $path) {
    $exists = is_dir($path);
    $writable = is_writable($path);
    $color = ($exists && $writable) ? 'green' : 'red';
    echo "<p style='color:$color'>$name: " . ($exists ? '✓ EXISTS' : '✗ MISSING') . 
         ($exists ? ($writable ? ' ✓ WRITABLE' : ' ✗ READ-ONLY') : '') . "</p>";
}

// 2. Check database tables
echo "<h2>2. Database Tables</h2>";
$db = Database::getInstance();
$tables = ['venue_images', 'court_images'];
foreach ($tables as $table) {
    $exists = $db->fetch("SHOW TABLES LIKE '$table'");
    $color = $exists ? 'green' : 'red';
    echo "<p style='color:$color'>$table: " . ($exists ? '✓ EXISTS' : '✗ MISSING') . "</p>";
}

// 3. Check PHP settings
echo "<h2>3. PHP Upload Settings</h2>";
echo "<p>file_uploads: " . (ini_get('file_uploads') ? '✓ ON' : '✗ OFF') . "</p>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>max_execution_time: " . ini_get('max_execution_time') . "s</p>";

// 4. Check user session
echo "<h2>4. User Session</h2>";
echo "<p>User ID: " . $_SESSION['user']['id'] . "</p>";
echo "<p>Role: " . $_SESSION['user']['role'] . "</p>";
echo "<p>Name: " . $_SESSION['user']['name'] . "</p>";

// 5. Test venues
echo "<h2>5. Your Venues</h2>";
$venues = $db->fetchAll(
    "SELECT id, name FROM venues WHERE owner_id = ? AND deleted_at IS NULL LIMIT 5",
    [$_SESSION['user']['id']]
);
if (empty($venues)) {
    echo "<p style='color:orange'>No venues found for your account</p>";
} else {
    echo "<ul>";
    foreach ($venues as $v) {
        echo "<li><a href='/admin/venues/{$v['id']}'>Venue #{$v['id']}: {$v['name']}</a></li>";
    }
    echo "</ul>";
}

// 6. Recent upload attempts (from logs)
echo "<h2>6. Recent Upload Logs</h2>";
$logFile = __DIR__ . '/storage/logs/' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $uploadLogs = array_filter(
        explode("\n", $logs),
        fn($line) => strpos($line, 'image upload') !== false
    );
    $recentLogs = array_slice(array_reverse($uploadLogs), 0, 5);
    
    if (empty($recentLogs)) {
        echo "<p>No upload attempts found today</p>";
    } else {
        echo "<pre style='background:#f5f5f5;padding:10px;font-size:11px'>";
        echo implode("\n", $recentLogs);
        echo "</pre>";
    }
} else {
    echo "<p>No log file for today</p>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li><a href='/admin/venues'>Go to Venues</a></li>";
echo "<li><a href='/admin/bookings/slots'>Go to Booking Slots</a></li>";
echo "<li><a href='/admin'>Go to Dashboard</a></li>";
echo "</ul>";

echo "</body></html>";
