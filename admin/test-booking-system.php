<?php
/**
 * Booking System Test Script
 * Run this file to verify the booking system is configured correctly
 * URL: http://localhost/admin/test-booking-system.php
 */

require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Config.php';

use App\Core\Database;

$db = Database::getInstance();

$results = [];
$allPassed = true;

// ============================================================
// TEST 1: Database Connection
// ============================================================
try {
    $test = $db->fetchColumn("SELECT 1");
    $results[] = ['test' => 'Database Connection', 'status' => 'PASS', 'message' => 'Connected successfully'];
} catch (Exception $e) {
    $results[] = ['test' => 'Database Connection', 'status' => 'FAIL', 'message' => $e->getMessage()];
    $allPassed = false;
}

// ============================================================
// TEST 2: Required Tables Exist
// ============================================================
$requiredTables = ['venues', 'courts', 'bookings', 'sports', 'users'];
foreach ($requiredTables as $table) {
    try {
        $exists = $db->fetchColumn("SHOW TABLES LIKE '{$table}'");
        if ($exists) {
            $results[] = ['test' => "Table: {$table}", 'status' => 'PASS', 'message' => 'Exists'];
        } else {
            $results[] = ['test' => "Table: {$table}", 'status' => 'FAIL', 'message' => 'Not found'];
            $allPassed = false;
        }
    } catch (Exception $e) {
        $results[] = ['test' => "Table: {$table}", 'status' => 'FAIL', 'message' => $e->getMessage()];
        $allPassed = false;
    }
}

// ============================================================
// TEST 3: Venues Data
// ============================================================
try {
    $venueCount = (int) $db->fetchColumn("SELECT COUNT(*) FROM venues WHERE deleted_at IS NULL");
    if ($venueCount > 0) {
        $results[] = ['test' => 'Venues Data', 'status' => 'PASS', 'message' => "{$venueCount} venues found"];
    } else {
        $results[] = ['test' => 'Venues Data', 'status' => 'WARN', 'message' => 'No venues in database'];
    }
} catch (Exception $e) {
    $results[] = ['test' => 'Venues Data', 'status' => 'FAIL', 'message' => $e->getMessage()];
    $allPassed = false;
}

// ============================================================
// TEST 4: Courts Data
// ============================================================
try {
    $courtCount = (int) $db->fetchColumn("SELECT COUNT(*) FROM courts WHERE deleted_at IS NULL");
    if ($courtCount > 0) {
        $results[] = ['test' => 'Courts Data', 'status' => 'PASS', 'message' => "{$courtCount} courts found"];
    } else {
        $results[] = ['test' => 'Courts Data', 'status' => 'WARN', 'message' => 'No courts in database'];
    }
} catch (Exception $e) {
    $results[] = ['test' => 'Courts Data', 'status' => 'FAIL', 'message' => $e->getMessage()];
    $allPassed = false;
}

// ============================================================
// TEST 5: Sports Data
// ============================================================
try {
    $sportCount = (int) $db->fetchColumn("SELECT COUNT(*) FROM sports WHERE is_active = 1");
    if ($sportCount > 0) {
        $results[] = ['test' => 'Sports Data', 'status' => 'PASS', 'message' => "{$sportCount} sports found"];
    } else {
        $results[] = ['test' => 'Sports Data', 'status' => 'WARN', 'message' => 'No sports in database'];
    }
} catch (Exception $e) {
    $results[] = ['test' => 'Sports Data', 'status' => 'FAIL', 'message' => $e->getMessage()];
    $allPassed = false;
}

// ============================================================
// TEST 6: Bookings Data
// ============================================================
try {
    $bookingCount = (int) $db->fetchColumn("SELECT COUNT(*) FROM bookings");
    $results[] = ['test' => 'Bookings Data', 'status' => 'PASS', 'message' => "{$bookingCount} bookings found"];
} catch (Exception $e) {
    $results[] = ['test' => 'Bookings Data', 'status' => 'FAIL', 'message' => $e->getMessage()];
    $allPassed = false;
}

// ============================================================
// TEST 7: Venue Opening/Closing Times
// ============================================================
try {
    $venuesWithTimes = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM venues 
         WHERE opening_time IS NOT NULL 
         AND closing_time IS NOT NULL 
         AND deleted_at IS NULL"
    );
    $totalVenues = (int) $db->fetchColumn("SELECT COUNT(*) FROM venues WHERE deleted_at IS NULL");
    
    if ($venuesWithTimes === $totalVenues && $totalVenues > 0) {
        $results[] = ['test' => 'Venue Times', 'status' => 'PASS', 'message' => 'All venues have opening/closing times'];
    } else {
        $results[] = ['test' => 'Venue Times', 'status' => 'WARN', 'message' => "{$venuesWithTimes}/{$totalVenues} venues have times set"];
    }
} catch (Exception $e) {
    $results[] = ['test' => 'Venue Times', 'status' => 'FAIL', 'message' => $e->getMessage()];
}

// ============================================================
// TEST 8: Court Foreign Keys
// ============================================================
try {
    $courtsWithVenue = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM courts c 
         INNER JOIN venues v ON c.venue_id = v.id 
         WHERE c.deleted_at IS NULL"
    );
    $totalCourts = (int) $db->fetchColumn("SELECT COUNT(*) FROM courts WHERE deleted_at IS NULL");
    
    if ($courtsWithVenue === $totalCourts && $totalCourts > 0) {
        $results[] = ['test' => 'Court Relations', 'status' => 'PASS', 'message' => 'All courts linked to venues'];
    } else {
        $results[] = ['test' => 'Court Relations', 'status' => 'WARN', 'message' => "{$courtsWithVenue}/{$totalCourts} courts linked"];
    }
} catch (Exception $e) {
    $results[] = ['test' => 'Court Relations', 'status' => 'FAIL', 'message' => $e->getMessage()];
}

// ============================================================
// TEST 9: Booking Validation
// ============================================================
try {
    $validBookings = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM bookings 
         WHERE venue_id IS NOT NULL 
         AND user_id IS NOT NULL 
         AND booking_date IS NOT NULL 
         AND start_time IS NOT NULL 
         AND end_time IS NOT NULL"
    );
    $totalBookings = (int) $db->fetchColumn("SELECT COUNT(*) FROM bookings");
    
    if ($validBookings === $totalBookings) {
        $results[] = ['test' => 'Booking Data Integrity', 'status' => 'PASS', 'message' => 'All bookings have required fields'];
    } else {
        $results[] = ['test' => 'Booking Data Integrity', 'status' => 'WARN', 'message' => "{$validBookings}/{$totalBookings} bookings valid"];
    }
} catch (Exception $e) {
    $results[] = ['test' => 'Booking Data Integrity', 'status' => 'FAIL', 'message' => $e->getMessage()];
}

// ============================================================
// TEST 10: API Endpoint
// ============================================================
$apiFile = __DIR__ . '/public/api/get-courts.php';
if (file_exists($apiFile)) {
    $results[] = ['test' => 'API Endpoint File', 'status' => 'PASS', 'message' => 'get-courts.php exists'];
} else {
    $results[] = ['test' => 'API Endpoint File', 'status' => 'FAIL', 'message' => 'get-courts.php not found'];
    $allPassed = false;
}

// ============================================================
// TEST 11: Controller Method
// ============================================================
$controllerFile = __DIR__ . '/app/Controllers/BookingController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    if (strpos($content, 'function slots') !== false) {
        $results[] = ['test' => 'slots() Method', 'status' => 'PASS', 'message' => 'BookingController::slots() exists'];
    } else {
        $results[] = ['test' => 'slots() Method', 'status' => 'FAIL', 'message' => 'Method not found'];
        $allPassed = false;
    }
} else {
    $results[] = ['test' => 'Controller File', 'status' => 'FAIL', 'message' => 'BookingController.php not found'];
    $allPassed = false;
}

// ============================================================
// TEST 12: View File
// ============================================================
$viewFile = __DIR__ . '/views/bookings/slots.php';
if (file_exists($viewFile)) {
    $results[] = ['test' => 'View File', 'status' => 'PASS', 'message' => 'slots.php exists'];
} else {
    $results[] = ['test' => 'View File', 'status' => 'FAIL', 'message' => 'views/bookings/slots.php not found'];
    $allPassed = false;
}

// ============================================================
// TEST 13: Users with Bookings
// ============================================================
try {
    $userCount = (int) $db->fetchColumn("SELECT COUNT(DISTINCT user_id) FROM bookings");
    $results[] = ['test' => 'Users with Bookings', 'status' => 'PASS', 'message' => "{$userCount} unique users have bookings"];
} catch (Exception $e) {
    $results[] = ['test' => 'Users with Bookings', 'status' => 'FAIL', 'message' => $e->getMessage()];
}

// ============================================================
// TEST 14: Today's Bookings
// ============================================================
try {
    $today = date('Y-m-d');
    $todayBookings = (int) $db->fetchColumn(
        "SELECT COUNT(*) FROM bookings WHERE booking_date = ?",
        [$today]
    );
    $results[] = ['test' => "Today's Bookings", 'status' => 'PASS', 'message' => "{$todayBookings} bookings for {$today}"];
} catch (Exception $e) {
    $results[] = ['test' => "Today's Bookings", 'status' => 'FAIL', 'message' => $e->getMessage()];
}

// ============================================================
// TEST 15: Booking Status Distribution
// ============================================================
try {
    $statuses = $db->fetchAll(
        "SELECT status, COUNT(*) as count 
         FROM bookings 
         GROUP BY status 
         ORDER BY count DESC"
    );
    if (!empty($statuses)) {
        $statusList = implode(', ', array_map(fn($s) => "{$s['status']}({$s['count']})", $statuses));
        $results[] = ['test' => 'Booking Statuses', 'status' => 'PASS', 'message' => $statusList];
    } else {
        $results[] = ['test' => 'Booking Statuses', 'status' => 'PASS', 'message' => 'No bookings yet'];
    }
} catch (Exception $e) {
    $results[] = ['test' => 'Booking Statuses', 'status' => 'FAIL', 'message' => $e->getMessage()];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking System Test Results</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0d1612 0%, #1a2e23 100%);
            color: #f0fdf4;
            padding: 2rem;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 1rem;
            color: #22c55e;
            font-size: 2.5rem;
        }
        
        .summary {
            background: rgba(15, 25, 18, 0.95);
            border: 2px solid;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .summary.pass {
            border-color: #22c55e;
        }
        
        .summary.fail {
            border-color: #ef4444;
        }
        
        .summary h2 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        
        .summary.pass h2 {
            color: #22c55e;
        }
        
        .summary.fail h2 {
            color: #ef4444;
        }
        
        .test-grid {
            display: grid;
            gap: 1rem;
        }
        
        .test-card {
            background: rgba(15, 25, 18, 0.95);
            border: 2px solid rgba(134, 168, 146, 0.15);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
        }
        
        .test-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
        }
        
        .test-status {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        
        .test-status.PASS {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 2px solid #22c55e;
        }
        
        .test-status.FAIL {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 2px solid #ef4444;
        }
        
        .test-status.WARN {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border: 2px solid #f59e0b;
        }
        
        .test-content {
            flex: 1;
        }
        
        .test-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: #f0fdf4;
        }
        
        .test-message {
            color: #a3c4af;
            font-size: 0.95rem;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: rgba(15, 25, 18, 0.95);
            border: 1px solid rgba(134, 168, 146, 0.15);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-value.pass {
            color: #22c55e;
        }
        
        .stat-value.fail {
            color: #ef4444;
        }
        
        .stat-value.warn {
            color: #f59e0b;
        }
        
        .stat-label {
            color: #a3c4af;
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: #22c55e;
            color: #0d1612;
            border: 2px solid #22c55e;
        }
        
        .btn-primary:hover {
            background: transparent;
            color: #22c55e;
        }
        
        .btn-secondary {
            background: transparent;
            color: #a3c4af;
            border: 2px solid #a3c4af;
        }
        
        .btn-secondary:hover {
            background: #a3c4af;
            color: #0d1612;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Booking System Test Results</h1>
        
        <div class="summary <?= $allPassed ? 'pass' : 'fail' ?>">
            <h2><?= $allPassed ? '✅ ALL TESTS PASSED' : '❌ SOME TESTS FAILED' ?></h2>
            <p>System Status: <?= $allPassed ? 'READY FOR PRODUCTION' : 'REQUIRES ATTENTION' ?></p>
        </div>
        
        <div class="stats">
            <?php
            $passCount = count(array_filter($results, fn($r) => $r['status'] === 'PASS'));
            $failCount = count(array_filter($results, fn($r) => $r['status'] === 'FAIL'));
            $warnCount = count(array_filter($results, fn($r) => $r['status'] === 'WARN'));
            ?>
            <div class="stat-card">
                <div class="stat-value pass"><?= $passCount ?></div>
                <div class="stat-label">Tests Passed</div>
            </div>
            <div class="stat-card">
                <div class="stat-value fail"><?= $failCount ?></div>
                <div class="stat-label">Tests Failed</div>
            </div>
            <div class="stat-card">
                <div class="stat-value warn"><?= $warnCount ?></div>
                <div class="stat-label">Warnings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= count($results) ?></div>
                <div class="stat-label">Total Tests</div>
            </div>
        </div>
        
        <div class="test-grid">
            <?php foreach ($results as $result): ?>
            <div class="test-card">
                <div class="test-status <?= $result['status'] ?>">
                    <?= $result['status'] ?>
                </div>
                <div class="test-content">
                    <div class="test-name"><?= htmlspecialchars($result['test']) ?></div>
                    <div class="test-message"><?= htmlspecialchars($result['message']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="action-buttons">
            <a href="/admin/bookings/slots" class="btn btn-primary">
                📅 Open Booking Slots
            </a>
            <a href="/admin/bookings" class="btn btn-secondary">
                📋 View All Bookings
            </a>
            <a href="javascript:location.reload()" class="btn btn-secondary">
                🔄 Run Tests Again
            </a>
        </div>
    </div>
</body>
</html>
