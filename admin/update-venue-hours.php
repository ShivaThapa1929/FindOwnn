<?php
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Config.php';

use App\Core\Database;

$db = Database::getInstance();

// Update all venues to be open 24 hours (12 AM to 11:59 PM)
$result = $db->execute(
    "UPDATE venues SET opening_time = '00:00:00', closing_time = '23:59:59' WHERE deleted_at IS NULL"
);

echo "<h2>Venue Hours Updated</h2>";
echo "<p>All venues now open from 12:00 AM (00:00) to 11:59 PM (23:59)</p>";

// Show updated venues
$venues = $db->fetchAll("SELECT id, name, opening_time, closing_time FROM venues WHERE deleted_at IS NULL");
echo "<h3>Updated Venues:</h3>";
echo "<table border='1' style='border-collapse: collapse; padding: 5px;'>";
echo "<tr><th>ID</th><th>Name</th><th>Opening Time</th><th>Closing Time</th></tr>";
foreach ($venues as $v) {
    echo "<tr>";
    echo "<td>{$v['id']}</td>";
    echo "<td>{$v['name']}</td>";
    echo "<td>{$v['opening_time']}</td>";
    echo "<td>{$v['closing_time']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><a href='/findownn_website/admin/bookings/slots'>Go to Booking Slots</a>";
