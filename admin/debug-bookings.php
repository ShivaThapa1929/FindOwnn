<?php
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Config.php';

use App\Core\Database;

$db = Database::getInstance();

echo "<h2>All Bookings</h2>";
$bookings = $db->fetchAll("SELECT * FROM bookings ORDER BY booking_date DESC, start_time DESC LIMIT 10");
echo "<pre>";
print_r($bookings);
echo "</pre>";

echo "<h2>Today's Bookings</h2>";
$today = date('Y-m-d');
$todayBookings = $db->fetchAll("SELECT * FROM bookings WHERE booking_date = ?", [$today]);
echo "<pre>";
print_r($todayBookings);
echo "</pre>";
