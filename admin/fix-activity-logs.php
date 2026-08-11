<?php
// Bootstrap the application
require_once __DIR__ . '/app/Core/Config.php';
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();

echo "<h2>Fixing Activity Log Descriptions</h2>";
echo "<pre style='background:#1a1a2e;color:#e2e8f0;padding:20px;border-radius:8px;'>";

// Get all activity logs
$activities = $db->fetchAll("SELECT id, description FROM activity_logs ORDER BY created_at DESC");

$updated = 0;

foreach ($activities as $activity) {
    $oldDesc = $activity['description'];
    $newDesc = $oldDesc;
    
    // Pattern 1: "Offline booking OFL-XXX created for NAME at venue #X, court #Y"
    if (preg_match('/Offline booking ([\w-]+) created for (.+?) at venue #(\d+), court #(\d+)/', $oldDesc, $matches)) {
        $newDesc = "Created booking {$matches[1]} for {$matches[2]}";
    }
    // Pattern 2: "Offline booking OFL-XXX created for NAME at venue #X"
    elseif (preg_match('/Offline booking ([\w-]+) created for (.+?) at venue #(\d+)/', $oldDesc, $matches)) {
        $newDesc = "Created booking {$matches[1]} for {$matches[2]}";
    }
    // Pattern 3: "Booking #XXX status → YYY"
    elseif (preg_match('/Booking #([\w-]+) status → (.+)/', $oldDesc, $matches)) {
        $newDesc = "{$matches[1]} → {$matches[2]}";
    }
    
    // Update if changed
    if ($newDesc !== $oldDesc) {
        $db->execute(
            "UPDATE activity_logs SET description = ? WHERE id = ?",
            [$newDesc, $activity['id']]
        );
        echo "✓ Updated: '{$oldDesc}' → '{$newDesc}'\n";
        $updated++;
    }
}

echo "\n========================================\n";
echo "Total activities processed: " . count($activities) . "\n";
echo "Total updated: {$updated}\n";
echo "========================================\n";

echo "</pre>";

echo "<p><a href='/findownn_website/admin/dashboard'>← Back to Dashboard</a></p>";
