<?php
/**
 * Debug Script for Booking Slots
 * Shows actual bookings and how they match with time slots
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

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get parameters
    $venue_id = $_GET['venue_id'] ?? null;
    $court_id = $_GET['court_id'] ?? null;
    $date = $_GET['date'] ?? date('Y-m-d');

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Booking Slots Debug</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Courier New', monospace; background: #0a0f0b; color: #f0fdf4; padding: 20px; }
            .container { max-width: 1400px; margin: 0 auto; background: rgba(15,25,18,0.95); padding: 30px; border-radius: 8px; }
            h1 { color: #22c55e; margin-bottom: 20px; }
            h2 { color: #3b82f6; margin: 30px 0 15px 0; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
            h3 { color: #f59e0b; margin: 20px 0 10px 0; }
            .form-group { margin-bottom: 15px; }
            label { display: block; color: #a3c4af; margin-bottom: 5px; font-weight: bold; }
            select, input { padding: 10px; background: #0d1510; border: 1px solid #86a892; color: #f0fdf4; border-radius: 5px; font-size: 14px; }
            button { padding: 10px 20px; background: #22c55e; color: #0a0f0b; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
            button:hover { background: #16a34a; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { padding: 12px; text-align: left; border: 1px solid #86a892; }
            th { background: #0d1510; color: #22c55e; font-weight: bold; }
            td { background: rgba(15,25,18,0.5); }
            .success { color: #22c55e; font-weight: bold; }
            .error { color: #ef4444; font-weight: bold; }
            .warning { color: #f59e0b; font-weight: bold; }
            .info { color: #3b82f6; font-weight: bold; }
            pre { background: #0d1510; padding: 15px; border-radius: 5px; overflow-x: auto; border: 1px solid #86a892; margin: 10px 0; }
            .slot-row { transition: background 0.2s; }
            .slot-row:hover { background: rgba(34,197,94,0.1); }
            .slot-row.booked { background: rgba(239,68,68,0.1); }
            .slot-row.booked:hover { background: rgba(239,68,68,0.2); }
        </style>
    </head>
    <body>
    <div class="container">
        <h1>🔍 Booking Slots Debug Tool</h1>

        <form method="GET" style="background: #0d1510; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px;">
                <div class="form-group">
                    <label>Venue:</label>
                    <select name="venue_id" id="venue_id" onchange="loadCourts(this.value)">
                        <option value="">Select Venue</option>
                        <?php
                        $venues = $pdo->query("SELECT id, name FROM venues WHERE deleted_at IS NULL ORDER BY name")->fetchAll();
                        foreach ($venues as $v) {
                            $selected = $venue_id == $v['id'] ? 'selected' : '';
                            echo "<option value='{$v['id']}' {$selected}>{$v['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Court:</label>
                    <select name="court_id" id="court_id">
                        <option value="">Select Court</option>
                        <?php
                        if ($venue_id) {
                            $courts = $pdo->prepare("SELECT id, name, court_number FROM courts WHERE venue_id = ? AND deleted_at IS NULL");
                            $courts->execute([$venue_id]);
                            foreach ($courts->fetchAll() as $c) {
                                $selected = $court_id == $c['id'] ? 'selected' : '';
                                echo "<option value='{$c['id']}' {$selected}>Court {$c['court_number']} - {$c['name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>">
                </div>
                <div class="form-group" style="align-self: end;">
                    <button type="submit">🔍 Debug</button>
                </div>
            </div>
        </form>

        <?php if ($venue_id && $court_id): 
            // Get venue and court info
            $venue = $pdo->prepare("SELECT * FROM venues WHERE id = ?");
            $venue->execute([$venue_id]);
            $venue = $venue->fetch();

            $court = $pdo->prepare("SELECT c.*, s.name as sport_name FROM courts c LEFT JOIN sports s ON c.sport_id = s.id WHERE c.id = ?");
            $court->execute([$court_id]);
            $court = $court->fetch();

            // Get all bookings for this court on this date
            $bookings = $pdo->prepare("
                SELECT b.*, u.name as user_name 
                FROM bookings b 
                LEFT JOIN users u ON b.user_id = u.id
                WHERE b.court_id = ? AND b.booking_date = ?
                ORDER BY b.start_time
            ");
            $bookings->execute([$court_id, $date]);
            $bookings = $bookings->fetchAll();
        ?>

        <h2>📊 Current Selection</h2>
        <table>
            <tr><th>Venue</th><td><?php echo htmlspecialchars($venue['name']); ?></td></tr>
            <tr><th>Court</th><td>Court <?php echo $court['court_number']; ?> - <?php echo htmlspecialchars($court['name']); ?></td></tr>
            <tr><th>Sport</th><td><?php echo htmlspecialchars($court['sport_name'] ?? 'N/A'); ?></td></tr>
            <tr><th>Date</th><td><?php echo date('l, F j, Y', strtotime($date)); ?></td></tr>
        </table>

        <h2>📅 All Bookings for This Court on This Date</h2>
        <?php if (empty($bookings)): ?>
            <p class="warning">⚠️ No bookings found for this court on this date.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?php echo $b['id']; ?></td>
                            <td class="info"><?php echo htmlspecialchars($b['booking_reference']); ?></td>
                            <td><?php echo htmlspecialchars($b['user_name'] ?: 'N/A'); ?></td>
                            <td class="success"><?php echo date('g:i A', strtotime($b['start_time'])); ?></td>
                            <td class="error"><?php echo date('g:i A', strtotime($b['end_time'])); ?></td>
                            <td><?php echo htmlspecialchars($b['status']); ?></td>
                            <td>₹<?php echo number_format($b['amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>🔍 Booking Detection Logic Test</h2>
        <table>
            <thead>
                <tr>
                    <th>Time Slot</th>
                    <th>Slot Start</th>
                    <th>Slot End</th>
                    <th>Status</th>
                    <th>Matched Booking</th>
                    <th>SQL Query Test</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Generate 24 hour slots
                $currentDate = strtotime($date . ' 00:00:00');
                $endDate = strtotime($date . ' 23:00:00');

                while ($currentDate <= $endDate) {
                    $slotStart = date('H:i:00', $currentDate);
                    $slotEnd = date('H:i:00', strtotime('+1 hour', $currentDate));
                    
                    // Test the booking detection query
                    $stmt = $pdo->prepare("
                        SELECT b.id, b.booking_reference, b.start_time, b.end_time, b.status
                        FROM bookings b 
                        WHERE b.court_id = ? 
                        AND b.booking_date = ? 
                        AND b.start_time < ? 
                        AND b.end_time > ?
                        AND b.status IN ('confirmed', 'in_progress', 'pending')
                        LIMIT 1
                    ");
                    $stmt->execute([$court_id, $date, $slotEnd, $slotStart]);
                    $booking = $stmt->fetch();
                    
                    $isBooked = $booking ? true : false;
                    $rowClass = $isBooked ? 'booked' : '';
                    
                    echo "<tr class='slot-row {$rowClass}'>";
                    echo "<td><strong>" . date('g:i A', $currentDate) . " - " . date('g:i A', strtotime('+1 hour', $currentDate)) . "</strong></td>";
                    echo "<td class='success'>{$slotStart}</td>";
                    echo "<td class='error'>{$slotEnd}</td>";
                    
                    if ($isBooked) {
                        echo "<td class='error'>❌ BOOKED</td>";
                        echo "<td><strong>{$booking['booking_reference']}</strong><br>";
                        echo "<small>" . date('g:i A', strtotime($booking['start_time'])) . " - " . date('g:i A', strtotime($booking['end_time'])) . "</small></td>";
                        echo "<td class='success'>✓ Query matched</td>";
                    } else {
                        echo "<td class='success'>✓ Available</td>";
                        echo "<td>-</td>";
                        echo "<td class='info'>No overlap found</td>";
                    }
                    
                    echo "</tr>";
                    
                    $currentDate = strtotime('+1 hour', $currentDate);
                }
                ?>
            </tbody>
        </table>

        <h3>📝 SQL Query Used:</h3>
        <pre>SELECT b.id, b.booking_reference, b.start_time, b.end_time, b.status
FROM bookings b 
WHERE b.court_id = <span class="info"><?php echo $court_id; ?></span> 
AND b.booking_date = <span class="info">'<?php echo $date; ?>'</span> 
AND b.start_time &lt; <span class="info">slot_end</span> 
AND b.end_time &gt; <span class="info">slot_start</span>
AND b.status IN ('confirmed', 'in_progress', 'pending')
LIMIT 1</pre>

        <h3>🧪 Test Cases:</h3>
        <pre><span class="success">✓ Booking 09:00 - 10:00 should block slot 09:00 - 10:00</span>
<span class="success">✓ Booking 09:30 - 10:30 should block slots 09:00 - 10:00 AND 10:00 - 11:00</span>
<span class="success">✓ Booking 09:00 - 12:00 should block slots 09:00 - 10:00, 10:00 - 11:00, 11:00 - 12:00</span></pre>

        <?php endif; ?>
    </div>

    <script>
    function loadCourts(venueId) {
        if (!venueId) {
            document.getElementById('court_id').innerHTML = '<option value="">Select Court</option>';
            return;
        }
        
        fetch('/findownn_website/admin/api/get-courts.php?venue_id=' + venueId)
            .then(res => res.json())
            .then(data => {
                let html = '<option value="">Select Court</option>';
                data.forEach(court => {
                    html += `<option value="${court.id}">Court ${court.court_number} - ${court.name}</option>`;
                });
                document.getElementById('court_id').innerHTML = html;
            })
            .catch(err => console.error('Error loading courts:', err));
    }
    </script>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo "<div style='color: #ef4444; padding: 20px; background: #0a0f0b; font-family: monospace;'>";
    echo "<h2>❌ Database Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
