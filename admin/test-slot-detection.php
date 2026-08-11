<?php
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Config.php';

use App\Core\Database;

$db = Database::getInstance();

$court_id = $_GET['court_id'] ?? 1;
$date = $_GET['date'] ?? date('Y-m-d');

echo "<h2>Testing Slot Detection</h2>";
echo "<p>Court ID: {$court_id}, Date: {$date}</p>";

echo "<h3>All bookings for this court on this date:</h3>";
$bookings = $db->fetchAll(
    "SELECT * FROM bookings WHERE court_id = ? AND booking_date = ?",
    [$court_id, $date]
);
echo "<pre>";
print_r($bookings);
echo "</pre>";

echo "<h3>Testing slot 09:00 - 10:00:</h3>";
$slotStart = '09:00:00';
$slotEnd = '10:00:00';

$booking = $db->fetch(
    "SELECT * FROM bookings 
     WHERE court_id = ? 
     AND booking_date = ? 
     AND (
         (start_time < ? AND end_time > ?)
         OR (start_time >= ? AND start_time < ?)
         OR (end_time > ? AND end_time <= ?)
     )
     AND status IN ('confirmed', 'in_progress', 'pending')",
    [
        $court_id, 
        $date, 
        $slotEnd, $slotStart,
        $slotStart, $slotEnd,
        $slotStart, $slotEnd
    ]
);

echo "<pre>";
print_r($booking);
echo "</pre>";

if ($booking) {
    echo "<p style='color: red; font-weight: bold;'>SLOT IS BOOKED!</p>";
} else {
    echo "<p style='color: green; font-weight: bold;'>SLOT IS AVAILABLE</p>";
}

echo "<h3>Testing slot 10:00 - 11:00:</h3>";
$slotStart = '10:00:00';
$slotEnd = '11:00:00';

$booking2 = $db->fetch(
    "SELECT * FROM bookings 
     WHERE court_id = ? 
     AND booking_date = ? 
     AND (
         (start_time < ? AND end_time > ?)
         OR (start_time >= ? AND start_time < ?)
         OR (end_time > ? AND end_time <= ?)
     )
     AND status IN ('confirmed', 'in_progress', 'pending')",
    [
        $court_id, 
        $date, 
        $slotEnd, $slotStart,
        $slotStart, $slotEnd,
        $slotStart, $slotEnd
    ]
);

echo "<pre>";
print_r($booking2);
echo "</pre>";

if ($booking2) {
    echo "<p style='color: red; font-weight: bold;'>SLOT IS BOOKED!</p>";
} else {
    echo "<p style='color: green; font-weight: bold;'>SLOT IS AVAILABLE</p>";
}
