<?php
require_once __DIR__ . '/app/Core/Config.php';
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();

echo "<h2>Booking Price Verification</h2>";
echo "<pre style='background:#1a1a2e;color:#e2e8f0;padding:20px;border-radius:8px;'>";

$bookings = $db->fetchAll(
    "SELECT b.booking_reference, b.amount, b.price_per_hour, b.total_hours,
            b.subtotal, b.discount_amount, b.discount_percent,
            c.name as court_name, c.price_per_hour as court_rate,
            v.name as venue_name
     FROM bookings b
     LEFT JOIN courts c ON b.court_id = c.id
     LEFT JOIN venues v ON b.venue_id = v.id
     ORDER BY b.created_at DESC
     LIMIT 10"
);

echo "Recent Bookings with Pricing:\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($bookings as $b) {
    echo "Ref: {$b['booking_reference']}\n";
    echo "Venue: {$b['venue_name']}\n";
    echo "Court: {$b['court_name']}\n";
    echo "Court Rate: ₹{$b['court_rate']}/hr\n";
    echo "Stored Rate: ₹{$b['price_per_hour']}/hr\n";
    echo "Duration: {$b['total_hours']} hours\n";
    echo "Subtotal: ₹{$b['subtotal']}\n";
    
    if ($b['discount_amount'] > 0) {
        echo "Discount: ₹{$b['discount_amount']} ({$b['discount_percent']}%)\n";
    }
    
    echo "Total: ₹{$b['amount']}\n";
    echo str_repeat("-", 80) . "\n\n";
}

echo "</pre>";
echo "<p><a href='/findownn_website/admin/bookings'>← Back to Bookings</a></p>";
