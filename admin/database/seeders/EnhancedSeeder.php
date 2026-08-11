<?php

namespace Database\Seeders;

use App\Core\Database;

/**
 * Enhanced Seeder — Seeds data for multi-venue, multi-court structure
 * Run after DatabaseSeeder
 */
class EnhancedSeeder
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function run(): void
    {
        echo "🌱 Seeding enhanced structure...\n";
        $this->updateUsersWithWhatsApp();
        $this->seedVenueSports();
        $this->seedCourts();
        $this->seedCourtImages();
        $this->updateBookingsWithCourts();
        echo "✅ Enhanced seeding complete.\n";
    }

    private function updateUsersWithWhatsApp(): void
    {
        // Update existing venue owners with WhatsApp numbers
        $this->db->execute(
            "UPDATE users SET whatsapp_number = phone, whatsapp_opt_in = 1 WHERE role = 'venue_owner' AND whatsapp_number IS NULL"
        );

        // Add some player accounts with WhatsApp
        $players = [
            ['name' => 'Virat Kumar',    'email' => 'virat@player.com',    'phone' => '+919876543220', 'whatsapp' => '+919876543220'],
            ['name' => 'Rohit Sharma',   'email' => 'rohit@player.com',    'phone' => '+919876543221', 'whatsapp' => '+919876543221'],
            ['name' => 'Smriti Patel',   'email' => 'smriti@player.com',   'phone' => '+919876543222', 'whatsapp' => '+919876543222'],
            ['name' => 'Sakshi Verma',   'email' => 'sakshi@player.com',   'phone' => '+919876543223', 'whatsapp' => '+919876543223'],
            ['name' => 'Arjun Singh',    'email' => 'arjun@player.com',    'phone' => '+919876543224', 'whatsapp' => '+919876543224'],
        ];

        $pass = password_hash('Player@123', PASSWORD_BCRYPT);

        foreach ($players as $p) {
            $exists = $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE email = ?", [$p['email']]);
            if (!$exists) {
                $this->db->execute(
                    "INSERT INTO users (name, email, password, phone, whatsapp_number, whatsapp_opt_in, role, status) 
                     VALUES (?,?,?,?,?,1,'venue_owner','active')",
                    [$p['name'], $p['email'], $pass, $p['phone'], $p['whatsapp']]
                );
            }
        }

        echo "  ✓ Users updated with WhatsApp numbers\n";
    }

    private function seedVenueSports(): void
    {
        // Get all venues
        $venues = $this->db->fetchAll("SELECT id FROM venues WHERE deleted_at IS NULL");

        if (empty($venues)) {
            echo "  ⚠  No venues found, skipping venue_sports seeding\n";
            return;
        }

        // Get sport IDs
        $boxCricket = $this->db->fetchColumn("SELECT id FROM sports WHERE slug = 'box-cricket'");
        $pickleball = $this->db->fetchColumn("SELECT id FROM sports WHERE slug = 'pickleball'");
        $football = $this->db->fetchColumn("SELECT id FROM sports WHERE slug = 'football'");

        foreach ($venues as $venue) {
            $venueId = $venue['id'];

            // Random sports per venue (1-3 sports)
            $sportIds = [];
            $numSports = rand(1, 3);

            if ($numSports >= 1) $sportIds[] = $boxCricket;
            if ($numSports >= 2) $sportIds[] = $pickleball;
            if ($numSports >= 3 && $football) $sportIds[] = $football;

            foreach ($sportIds as $sportId) {
                if (!$sportId) continue;

                $exists = $this->db->fetchColumn(
                    "SELECT COUNT(*) FROM venue_sports WHERE venue_id = ? AND sport_id = ?",
                    [$venueId, $sportId]
                );

                if (!$exists) {
                    $this->db->execute(
                        "INSERT INTO venue_sports (venue_id, sport_id) VALUES (?, ?)",
                        [$venueId, $sportId]
                    );
                }
            }
        }

        echo "  ✓ Venue sports mapped\n";
    }

    private function seedCourts(): void
    {
        // Get venues with their sports
        $venues = $this->db->fetchAll(
            "SELECT v.id as venue_id, v.name as venue_name, vs.sport_id 
             FROM venues v
             INNER JOIN venue_sports vs ON v.id = vs.venue_id
             WHERE v.deleted_at IS NULL"
        );

        if (empty($venues)) {
            echo "  ⚠  No venue-sport mappings found\n";
            return;
        }

        $courtTypes = [
            'Court A', 'Court B', 'Court C', 'Main Court', 'Practice Court',
            'Professional Court', 'Training Court', 'Premium Court'
        ];

        $surfaces = ['Artificial Turf', 'Concrete', 'Wood', 'Synthetic', 'Clay'];

        foreach ($venues as $venue) {
            // Create 1-3 courts per sport
            $numCourts = rand(1, 3);

            for ($i = 0; $i < $numCourts; $i++) {
                $courtName = $courtTypes[array_rand($courtTypes)];
                $courtNumber = 'C' . ($i + 1);

                $amenities = json_encode([
                    'Lighting',
                    'Seating',
                    'Water',
                    'Changing Room',
                    'Parking'
                ]);

                $equipment = json_encode([
                    'Balls',
                    'Nets',
                    'Scoreboards'
                ]);

                $exists = $this->db->fetchColumn(
                    "SELECT COUNT(*) FROM courts WHERE venue_id = ? AND sport_id = ? AND court_number = ?",
                    [$venue['venue_id'], $venue['sport_id'], $courtNumber]
                );

                if (!$exists) {
                    $this->db->execute(
                        "INSERT INTO courts (venue_id, sport_id, name, court_number, description, surface_type, 
                         dimensions, capacity, price_per_hour, amenities, equipment_provided, status, 
                         is_indoor, has_lighting, booking_slot_duration, sort_order)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                        [
                            $venue['venue_id'],
                            $venue['sport_id'],
                            $courtName,
                            $courtNumber,
                            "Professional grade court with all modern amenities",
                            $surfaces[array_rand($surfaces)],
                            '30x20 feet',
                            rand(10, 22),
                            rand(800, 1500),
                            $amenities,
                            $equipment,
                            'active',
                            rand(0, 1),
                            1,
                            60,
                            $i
                        ]
                    );
                }
            }
        }

        echo "  ✓ Courts seeded\n";
    }

    private function seedCourtImages(): void
    {
        $courts = $this->db->fetchAll("SELECT id FROM courts");

        if (empty($courts)) {
            echo "  ⚠  No courts found\n";
            return;
        }

        // Dummy image paths
        $images = [
            'uploads/courts/court_1.jpg',
            'uploads/courts/court_2.jpg',
            'uploads/courts/court_3.jpg',
            'uploads/courts/court_4.jpg',
        ];

        foreach ($courts as $court) {
            // Add 2-4 images per court
            $numImages = rand(2, 4);

            for ($i = 0; $i < $numImages; $i++) {
                $imageType = $i === 0 ? 'featured' : 'gallery';

                $exists = $this->db->fetchColumn(
                    "SELECT COUNT(*) FROM court_images WHERE court_id = ? AND sort_order = ?",
                    [$court['id'], $i]
                );

                if (!$exists) {
                    $this->db->execute(
                        "INSERT INTO court_images (court_id, image_path, caption, image_type, sort_order)
                         VALUES (?,?,?,?,?)",
                        [
                            $court['id'],
                            $images[array_rand($images)],
                            "Court view " . ($i + 1),
                            $imageType,
                            $i
                        ]
                    );
                }
            }
        }

        echo "  ✓ Court images seeded\n";
    }

    private function updateBookingsWithCourts(): void
    {
        // Update existing bookings with court and sport assignments
        $bookings = $this->db->fetchAll(
            "SELECT b.id as booking_id, b.venue_id 
             FROM bookings b 
             WHERE b.court_id IS NULL"
        );

        foreach ($bookings as $booking) {
            // Get a random court for this venue
            $court = $this->db->fetch(
                "SELECT id, sport_id FROM courts WHERE venue_id = ? ORDER BY RAND() LIMIT 1",
                [$booking['venue_id']]
            );

            if ($court) {
                $this->db->execute(
                    "UPDATE bookings SET court_id = ?, sport_id = ? WHERE id = ?",
                    [$court['id'], $court['sport_id'], $booking['booking_id']]
                );
            }
        }

        echo "  ✓ Bookings updated with court assignments\n";
    }
}
