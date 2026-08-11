<?php
/**
 * Add Sample Data for Testing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🎨 Adding Sample Data...\n";
echo "====================================\n\n";

// Load .env
$envFile = __DIR__ . '/.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if ($line && $line[0] !== '#' && strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

$host = $env['DB_HOST'] ?? 'localhost';
$dbName = $env['DB_DATABASE'] ?? 'findownn_admin';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbName;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Add Sports
    echo "1️⃣ Adding sports...\n";
    $sports = [
        ['Box Cricket', 'cricket', 'Cricket played in a box format', 1],
        ['Pickleball', 'pickleball', 'Fast-growing racket sport', 2],
        ['Football', 'football', '5-a-side football', 3],
        ['Badminton', 'badminton', 'Indoor badminton courts', 4],
    ];
    
    foreach ($sports as $sport) {
        $pdo->exec("INSERT IGNORE INTO sports (name, slug, description, created_at) 
                    VALUES ('$sport[0]', '$sport[1]', '$sport[2]', NOW())");
    }
    echo "   ✅ Sports added!\n\n";
    
    // Add Sample Venues
    echo "2️⃣ Adding sample venues...\n";
    
    // Get admin user ID
    $adminId = $pdo->query("SELECT id FROM users WHERE email = 'admin@findownn.com'")->fetchColumn();
    
    $venues = [
        [
            'name' => 'Bhuj Box Arena',
            'slug' => 'bhuj-box-arena',
            'description' => 'Premium box cricket ground with floodlights and modern facilities',
            'address' => 'Sanskar Nagar, Bhuj',
            'city' => 'Bhuj',
            'state' => 'Gujarat',
            'pincode' => '370001',
            'phone' => '9876543210',
            'email' => 'info@bhujboxarena.com',
            'latitude' => '23.2419997',
            'longitude' => '69.6669324',
            'price_per_hour' => 1000,
            'rating' => 4.9,
            'total_reviews' => 125,
            'opening_time' => '06:00',
            'closing_time' => '23:00',
            'status' => 'active'
        ],
        [
            'name' => 'Champion Turf',
            'slug' => 'champion-turf',
            'description' => 'Professional pickleball courts with indoor facilities',
            'address' => 'Madhapar Road, Bhuj',
            'city' => 'Bhuj',
            'state' => 'Gujarat',
            'pincode' => '370020',
            'phone' => '9876543211',
            'email' => 'info@championturf.com',
            'latitude' => '23.2599',
            'longitude' => '69.6522',
            'price_per_hour' => 800,
            'rating' => 4.8,
            'total_reviews' => 98,
            'opening_time' => '07:00',
            'closing_time' => '22:00',
            'status' => 'active'
        ],
        [
            'name' => 'Kutch Sports Hub',
            'slug' => 'kutch-sports-hub',
            'description' => 'Multi-sport facility with cricket, football and badminton',
            'address' => 'Mundra Road, Bhuj',
            'city' => 'Bhuj',
            'state' => 'Gujarat',
            'pincode' => '370001',
            'phone' => '9876543212',
            'email' => 'info@kutchsports.com',
            'latitude' => '23.2420',
            'longitude' => '69.6700',
            'price_per_hour' => 1200,
            'rating' => 4.7,
            'total_reviews' => 87,
            'opening_time' => '06:00',
            'closing_time' => '23:00',
            'status' => 'active'
        ]
    ];
    
    foreach ($venues as $venue) {
        $stmt = $pdo->prepare("
            INSERT INTO venues (
                owner_id, name, slug, description, address, city, state, pincode,
                contact_phone, contact_email, latitude, longitude, price_per_hour, rating, total_reviews,
                opening_time, closing_time, status, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
        ");
        
        $stmt->execute([
            $adminId,
            $venue['name'],
            $venue['slug'],
            $venue['description'],
            $venue['address'],
            $venue['city'],
            $venue['state'],
            $venue['pincode'],
            $venue['phone'],
            $venue['email'],
            $venue['latitude'],
            $venue['longitude'],
            $venue['price_per_hour'],
            $venue['rating'],
            $venue['total_reviews'],
            $venue['opening_time'],
            $venue['closing_time'],
            $venue['status']
        ]);
        
        $venueId = $pdo->lastInsertId();
        
        // Link to cricket sport for first and third venue
        if ($venue['name'] == 'Bhuj Box Arena' || $venue['name'] == 'Kutch Sports Hub') {
            $pdo->exec("INSERT INTO venue_sports (venue_id, sport_id) VALUES ($venueId, 1)");
        }
        
        // Link to pickleball for second venue
        if ($venue['name'] == 'Champion Turf') {
            $pdo->exec("INSERT INTO venue_sports (venue_id, sport_id) VALUES ($venueId, 2)");
        }
        
        echo "   ✅ Added: {$venue['name']}\n";
    }
    
    echo "\n3️⃣ Verifying data...\n";
    $venueCount = $pdo->query("SELECT COUNT(*) FROM venues")->fetchColumn();
    $sportCount = $pdo->query("SELECT COUNT(*) FROM sports")->fetchColumn();
    
    echo "   ✅ Venues: $venueCount\n";
    echo "   ✅ Sports: $sportCount\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 SAMPLE DATA ADDED!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "🔗 Test Now:\n";
    echo "   API: http://localhost:8000/api/v1/venues\n";
    echo "   Admin: http://localhost:8000/admin/\n";
    echo "   Website: http://localhost:8000/index.php\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
