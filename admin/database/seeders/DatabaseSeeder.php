<?php

namespace Database\Seeders;

use App\Core\Database;

/**
 * DatabaseSeeder — Seeds all required sample data.
 * Run: php migrate db:seed
 */
class DatabaseSeeder
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function run(): void
    {
        echo "🌱 Seeding database...\n";
        $this->seedSettings();
        $this->seedSubscriptionPlans();
        $this->seedUsers();
        $this->seedVenues();
        $this->seedSubscriptions();
        $this->seedBookings();
        echo "✅ Seeding complete.\n";
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'app_name',        'value' => 'Findownn Admin',    'group' => 'general',  'label' => 'App Name'],
            ['key' => 'app_logo',        'value' => '',                   'group' => 'general',  'label' => 'App Logo'],
            ['key' => 'contact_email',   'value' => 'findownn@gmail.com','group' => 'general',  'label' => 'Contact Email'],
            ['key' => 'contact_phone',   'value' => '+91 95583 46768',   'group' => 'general',  'label' => 'Phone'],
            ['key' => 'currency',        'value' => 'INR',               'group' => 'payment',  'label' => 'Currency'],
            ['key' => 'currency_symbol', 'value' => '₹',                 'group' => 'payment',  'label' => 'Currency Symbol'],
            ['key' => 'commission_pct',  'value' => '15',                'group' => 'payment',  'label' => 'Commission %'],
            ['key' => 'mail_from',       'value' => 'findownn@gmail.com','group'=>'mail',     'label' => 'Mail From'],
            ['key' => 'login_attempts',  'value' => '5',                 'group' => 'security', 'label' => 'Max Login Attempts'],
            ['key' => 'session_timeout', 'value' => '120',               'group' => 'security', 'label' => 'Session Timeout (min)'],
        ];

        foreach ($settings as $s) {
            $exists = $this->db->fetchColumn("SELECT COUNT(*) FROM settings WHERE `key` = ?", [$s['key']]);
            if (!$exists) {
                $this->db->execute(
                    "INSERT INTO settings (`key`, value, `group`, label, created_at, updated_at) VALUES (?,?,?,?,NOW(),NOW())",
                    [$s['key'], $s['value'], $s['group'], $s['label']]
                );
            }
        }
        echo "  ✓ Settings seeded\n";
    }

    private function seedSubscriptionPlans(): void
    {
        $setupScript = dirname(__DIR__, 2) . '/setup-subscription-plans.php';
        if (is_file($setupScript)) {
            require $setupScript;
            return;
        }

        echo "  ⚠ Run php admin/setup-subscription-plans.php to seed marketplace plans\n";
    }

    private function seedUsers(): void
    {
        $users = [
            ['name' => 'Super Admin',    'email' => 'superadmin@findownn.com', 'role' => 'super_admin', 'phone' => '+91 99999 00001'],
            ['name' => 'Findownn Admin', 'email' => 'admin@findownn.com',      'role' => 'admin',       'phone' => '+91 99999 00002'],
            ['name' => 'Rahul Patel',    'email' => 'rahul@venue.com',         'role' => 'venue_owner', 'phone' => '+91 98765 43210'],
            ['name' => 'Priya Shah',     'email' => 'priya@venue.com',         'role' => 'venue_owner', 'phone' => '+91 98765 43211'],
            ['name' => 'Amit Joshi',     'email' => 'amit@venue.com',          'role' => 'venue_owner', 'phone' => '+91 98765 43212'],
        ];

        $pass = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 12]);

        foreach ($users as $u) {
            $exists = $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE email = ?", [$u['email']]);
            if (!$exists) {
                $this->db->execute(
                    "INSERT INTO users (name, email, password, phone, role, status, created_at, updated_at)
                     VALUES (?,?,?,?,?,'active',NOW(),NOW())",
                    [$u['name'], $u['email'], $pass, $u['phone'], $u['role']]
                );
            }
        }
        echo "  ✓ Users seeded (password: Admin@123)\n";
    }

    private function seedVenues(): void
    {
        $ownerId = $this->db->fetchColumn("SELECT id FROM users WHERE role = 'venue_owner' LIMIT 1");
        if (!$ownerId) return;

        $venues = [
            ['name' => 'Bhuj Box Arena',     'city' => 'Bhuj', 'price' => 1000, 'status' => 'approved'],
            ['name' => 'Champion Pickleball', 'city' => 'Bhuj', 'price' => 800,  'status' => 'approved'],
            ['name' => 'Kutch Sports Hub',    'city' => 'Bhuj', 'price' => 1200, 'status' => 'pending'],
            ['name' => 'Smash & Play',        'city' => 'Bhuj', 'price' => 750,  'status' => 'approved'],
        ];

        foreach ($venues as $v) {
            $exists = $this->db->fetchColumn("SELECT COUNT(*) FROM venues WHERE name = ?", [$v['name']]);
            if (!$exists) {
                $this->db->execute(
                    "INSERT INTO venues (owner_id, name, slug, city, price_per_hour, status, verification_status, is_verified, created_at, updated_at)
                     VALUES (?,?,?,?,?,?,?,0,NOW(),NOW())",
                    [$ownerId, $v['name'], strtolower(str_replace(' ', '-', $v['name'])), $v['city'], $v['price'], $v['status'] === 'approved' ? 'active' : 'inactive', $v['status']]
                );
            }
        }
        echo "  ✓ Venues seeded\n";
    }

    private function seedSubscriptions(): void
    {
        $planId = $this->db->fetchColumn("SELECT id FROM subscription_plans WHERE slug IN ('professional', 'premium') ORDER BY FIELD(slug, 'professional', 'premium') LIMIT 1");
        $userId = $this->db->fetchColumn("SELECT id FROM users WHERE role = 'venue_owner' LIMIT 1");
        if (!$planId || !$userId) return;

        $exists = $this->db->fetchColumn("SELECT COUNT(*) FROM subscriptions WHERE user_id = ?", [$userId]);
        if (!$exists) {
            $this->db->execute(
                "INSERT INTO subscriptions (user_id, plan_id, status, starts_at, expires_at, amount_paid, invoice_number, created_at, updated_at)
                 VALUES (?,?,'active',NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), 2499, 'INV-SEED-0001', NOW(), NOW())",
                [$userId, $planId]
            );
        }
        echo "  ✓ Subscriptions seeded\n";
    }

    private function seedBookings(): void
    {
        $venueId = $this->db->fetchColumn("SELECT id FROM venues LIMIT 1");
        $userId  = $this->db->fetchColumn("SELECT id FROM users LIMIT 1");
        if (!$venueId || !$userId) return;

        $exists = $this->db->fetchColumn("SELECT COUNT(*) FROM bookings");
        if ($exists) return;

        for ($i = 1; $i <= 10; $i++) {
            $date = date('Y-m-d', strtotime("+{$i} days"));
            $this->db->execute(
                "INSERT INTO bookings (venue_id, user_id, booking_date, start_time, end_time, total_hours, amount, status, payment_status, booking_reference, created_at, updated_at)
                 VALUES (?,?,?,?,?,1,1000,'confirmed','paid',?,NOW(),NOW())",
                [$venueId, $userId, $date, '18:00:00', '19:00:00', 'BK-SEED-' . str_pad($i, 4, '0', STR_PAD_LEFT)]
            );
        }
        echo "  ✓ Sample bookings seeded\n";
    }
}
