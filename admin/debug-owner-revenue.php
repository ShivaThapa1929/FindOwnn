<?php
// Debug owner revenue data

require_once __DIR__ . '/app/Core/Config.php';
require_once __DIR__ . '/app/Core/Logger.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Session.php';

use App\Core\Database;
use App\Core\Config;
use App\Core\Session;

Session::start();

try {
    Config::load(__DIR__ . '/.env');
    $db = Database::getInstance();
    
    // Get logged in user
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        echo "❌ Not logged in\n";
        exit(1);
    }
    
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    echo "=== USER INFO ===\n";
    echo "User ID: {$user['id']}\n";
    echo "Name: {$user['name']}\n";
    echo "Role: {$user['role']}\n\n";
    
    if ($user['role'] !== 'venue_owner') {
        echo "❌ Not a venue owner\n";
        exit(1);
    }
    
    // Check venues
    echo "=== VENUES ===\n";
    $venues = $db->fetchAll("SELECT id, name, status FROM venues WHERE owner_id = ?", [$userId]);
    echo "Total venues: " . count($venues) . "\n";
    foreach ($venues as $v) {
        echo "  - {$v['name']} (ID: {$v['id']}, Status: {$v['status']})\n";
    }
    echo "\n";
    
    // Check bookings
    echo "=== BOOKINGS ===\n";
    $venueIds = array_column($venues, 'id');
    if (empty($venueIds)) {
        echo "No venues found for this owner\n\n";
    } else {
        $placeholders = implode(',', array_fill(0, count($venueIds), '?'));
        $bookings = $db->fetchAll(
            "SELECT b.*, v.name as venue_name 
             FROM bookings b 
             JOIN venues v ON b.venue_id = v.id 
             WHERE b.venue_id IN ($placeholders) 
             ORDER BY b.created_at DESC 
             LIMIT 10",
            $venueIds
        );
        
        echo "Total bookings: " . count($bookings) . "\n";
        foreach ($bookings as $b) {
            echo "  - {$b['booking_reference']} | {$b['venue_name']} | ₹{$b['amount']} | {$b['payment_status']} | {$b['booking_date']}\n";
        }
        echo "\n";
        
        // Check monthly revenue
        echo "=== MONTHLY REVENUE (Last 6 months) ===\n";
        $monthlyRev = $db->fetchAll(
            "SELECT DATE_FORMAT(booking_date,'%Y-%m') AS month,
                    SUM(CASE WHEN payment_status='paid' THEN amount ELSE 0 END) AS revenue,
                    COUNT(*) AS bookings
             FROM bookings
             WHERE venue_id IN ($placeholders)
             AND booking_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month ASC",
            $venueIds
        );
        
        if (empty($monthlyRev)) {
            echo "❌ No monthly revenue data found\n";
            echo "This is likely because:\n";
            echo "  1. No bookings exist for your venues\n";
            echo "  2. All bookings are older than 6 months\n";
            echo "  3. No bookings have been paid\n\n";
        } else {
            echo "Found " . count($monthlyRev) . " months with data:\n";
            foreach ($monthlyRev as $m) {
                echo "  - {$m['month']}: ₹{$m['revenue']} ({$m['bookings']} bookings)\n";
            }
            echo "\n";
        }
        
        // Check total stats
        echo "=== TOTAL STATS ===\n";
        $stats = $db->fetch(
            "SELECT
                COUNT(*)  AS total,
                SUM(status = 'confirmed') AS confirmed,
                SUM(status = 'cancelled') AS cancelled,
                SUM(CASE WHEN payment_status = 'paid' THEN amount ELSE 0 END) AS total_revenue,
                SUM(CASE WHEN payment_status = 'paid'
                    AND booking_date >= DATE_FORMAT(NOW(),'%Y-%m-01')
                    THEN amount ELSE 0 END) AS monthly_revenue
             FROM bookings
             WHERE venue_id IN ($placeholders)",
            $venueIds
        );
        
        echo "Total bookings: {$stats['total']}\n";
        echo "Confirmed: {$stats['confirmed']}\n";
        echo "Cancelled: {$stats['cancelled']}\n";
        echo "Total revenue: ₹{$stats['total_revenue']}\n";
        echo "This month revenue: ₹{$stats['monthly_revenue']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
