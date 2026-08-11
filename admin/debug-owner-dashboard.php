<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();

// Check if there's a logged-in owner (you may need to adjust this)
// For now, let's check all owners
echo "<h2>Owner Dashboard Data Debug</h2>";
echo "<pre style='background:#1a1a2e;color:#e2e8f0;padding:20px;border-radius:8px;'>";

// Get all venue owners
$owners = $db->fetchAll("SELECT id, name, email FROM users WHERE role = 'venue_owner' AND deleted_at IS NULL");

foreach ($owners as $owner) {
    echo "\n========================================\n";
    echo "Owner: {$owner['name']} (ID: {$owner['id']})\n";
    echo "========================================\n\n";
    
    // Get venues
    $venues = $db->fetchAll(
        "SELECT id, name, status, verification_status FROM venues WHERE owner_id = ? AND deleted_at IS NULL",
        [$owner['id']]
    );
    
    echo "Venues (" . count($venues) . "):\n";
    foreach ($venues as $v) {
        echo "  - {$v['name']} [ID: {$v['id']}] - Status: {$v['status']}, Verification: {$v['verification_status']}\n";
    }
    
    // Get bookings with details
    echo "\nBookings:\n";
    $bookings = $db->fetchAll(
        "SELECT b.id, b.booking_reference, b.booking_date, b.amount, b.status, b.payment_status,
                v.name as venue_name, b.created_at
         FROM bookings b
         JOIN venues v ON b.venue_id = v.id
         WHERE v.owner_id = ?
         ORDER BY b.booking_date DESC
         LIMIT 20",
        [$owner['id']]
    );
    
    if (empty($bookings)) {
        echo "  No bookings found.\n";
    } else {
        foreach ($bookings as $b) {
            echo "  - {$b['booking_reference']} | Date: {$b['booking_date']} | Amount: ₹{$b['amount']} | ";
            echo "Status: {$b['status']} | Payment: {$b['payment_status']} | Venue: {$b['venue_name']}\n";
        }
    }
    
    // Monthly revenue breakdown
    echo "\nMonthly Revenue (Last 6 months):\n";
    $monthlyRev = $db->fetchAll(
        "SELECT DATE_FORMAT(booking_date,'%Y-%m') AS month,
                DATE_FORMAT(booking_date,'%M %Y') AS month_name,
                SUM(CASE WHEN payment_status='paid' THEN amount ELSE 0 END) AS revenue,
                COUNT(*) AS total_bookings,
                COUNT(CASE WHEN payment_status='paid' THEN 1 END) AS paid_bookings
         FROM bookings
         WHERE venue_id IN (SELECT id FROM venues WHERE owner_id = ?)
         AND booking_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
         GROUP BY month, month_name ORDER BY month ASC",
        [$owner['id']]
    );
    
    if (empty($monthlyRev)) {
        echo "  No revenue data in last 6 months.\n";
    } else {
        foreach ($monthlyRev as $m) {
            echo "  - {$m['month_name']}: ₹{$m['revenue']} ({$m['paid_bookings']} paid / {$m['total_bookings']} total bookings)\n";
        }
    }
    
    // Total stats
    $stats = $db->fetch(
        "SELECT
            COUNT(*) AS total,
            SUM(status = 'confirmed') AS confirmed,
            SUM(CASE WHEN payment_status = 'paid' THEN amount ELSE 0 END) AS total_revenue,
            SUM(CASE WHEN payment_status = 'paid' AND booking_date >= DATE_FORMAT(NOW(),'%Y-%m-01') THEN amount ELSE 0 END) AS monthly_revenue
         FROM bookings
         WHERE venue_id IN (SELECT id FROM venues WHERE owner_id = ?)",
        [$owner['id']]
    );
    
    echo "\nTotal Stats:\n";
    echo "  - Total Bookings: {$stats['total']}\n";
    echo "  - Confirmed: {$stats['confirmed']}\n";
    echo "  - Total Revenue: ₹{$stats['total_revenue']}\n";
    echo "  - This Month Revenue: ₹{$stats['monthly_revenue']}\n";
    
    echo "\n";
}

echo "</pre>";
