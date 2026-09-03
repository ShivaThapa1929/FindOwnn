<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\User;
use App\Models\Venue;
use App\Models\Booking;
use App\Models\Subscription;
use App\Models\ActivityLog;
use App\Services\OwnerRecommendationService;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $role = $this->user()['role'];

        if ($role === 'venue_owner') {
            $this->ownerDashboard();
            return;
        }

        $this->adminDashboard();
    }

    // ── Admin / Super Admin ───────────────────────────────────────
    private function adminDashboard(): void
    {
        $userModel  = new User();
        $venueModel = new Venue();
        $bookModel  = new Booking();
        $subModel   = new Subscription();
        $actModel   = new ActivityLog();

        $stats = [
            'total_users'     => $userModel->count('deleted_at IS NULL'),
            'total_owners'    => $userModel->count("role = 'venue_owner' AND deleted_at IS NULL"),
            'total_players'   => $userModel->countPlayers(),
            'total_venues'    => $venueModel->count('deleted_at IS NULL'),
            'pending_venues'  => $venueModel->count("verification_status = 'pending' AND deleted_at IS NULL"),
            'total_bookings'  => $bookModel->count(),
            'active_subs'     => $subModel->count("status = 'active'"),
            'expired_subs'    => $subModel->count("status = 'expired'"),
            'verified_venues' => $venueModel->count('is_verified = 1 AND deleted_at IS NULL'),
        ];

        // NEW: Court statistics
        $stats['total_courts'] = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM courts WHERE deleted_at IS NULL");
        $stats['active_courts'] = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM courts WHERE status = 'active' AND deleted_at IS NULL");

        // NEW: Today's stats
        $todayStats = $this->db->fetch(
            "SELECT 
                COUNT(*) as bookings_today,
                SUM(CASE WHEN payment_status='paid' THEN amount ELSE 0 END) as revenue_today,
                COUNT(CASE WHEN status='confirmed' THEN 1 END) as confirmed_today
             FROM bookings 
             WHERE booking_date = CURDATE()"
        );
        $stats['bookings_today'] = (int) ($todayStats['bookings_today'] ?? 0);
        $stats['revenue_today'] = $todayStats['revenue_today'] ?? 0;
        $stats['confirmed_today'] = (int) ($todayStats['confirmed_today'] ?? 0);

        // NEW: New registrations today
        $stats['users_today'] = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL"
        );

        // Revenue totals
        $revRow = $this->db->fetch(
            "SELECT
                SUM(CASE WHEN payment_status='paid' THEN amount ELSE 0 END) AS total_revenue,
                SUM(CASE WHEN payment_status='paid' AND booking_date >= DATE_FORMAT(NOW(),'%Y-%m-01') THEN amount ELSE 0 END) AS monthly_revenue
             FROM bookings"
        );
        $stats['total_revenue']   = $revRow['total_revenue']   ?? 0;
        $stats['monthly_revenue'] = $revRow['monthly_revenue'] ?? 0;

        // Growth percentages vs last month
        $lastMonthUsers = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE deleted_at IS NULL
             AND created_at >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH),'%Y-%m-01')
             AND created_at  < DATE_FORMAT(NOW(),'%Y-%m-01')"
        );
        $thisMonthUsers = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE deleted_at IS NULL
             AND created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')"
        );
        $stats['user_growth'] = $lastMonthUsers > 0
            ? round((($thisMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 1)
            : ($thisMonthUsers > 0 ? 100 : 0);

        $monthlyRev    = $bookModel->getMonthlyRevenue(6);
        $venueStats    = $venueModel->getStats();
        $bookStats     = $bookModel->getStats();
        $subStats      = $subModel->getStats();
        $recentUsers   = $userModel->getRecentUsers(6);
        $recentActivity = $actModel->getRecent(8);

        // Role-Based User Statistics
        $roleStats = [
            'super_admin' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'super_admin' AND deleted_at IS NULL"),
            'admin'       => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'admin' AND deleted_at IS NULL"),
            'venue_owner' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'venue_owner' AND deleted_at IS NULL"),
            'player'      => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE (role = 'player' OR role = 'customer' OR role = 'user') AND deleted_at IS NULL"),
            'pending_owner_verification' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'venue_owner' AND status = 'pending_email_verification' AND deleted_at IS NULL"),
        ];

        // Recent Role Logins & Accounts
        $recentRoleLogins = $this->db->fetchAll(
            "SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.email_verified_at, u.created_at, u.updated_at
             FROM users u
             WHERE u.deleted_at IS NULL
             ORDER BY u.id DESC LIMIT 8"
        );

        // Pending venues that need action
        $pendingVenues = $this->db->fetchAll(
            "SELECT v.id, v.name, v.city, v.address,
                    u.name AS owner_name, v.created_at,
                    GROUP_CONCAT(s.name SEPARATOR ', ') AS sports
             FROM venues v 
             JOIN users u ON v.owner_id = u.id
             LEFT JOIN venue_sports vs ON v.id = vs.venue_id
             LEFT JOIN sports s ON vs.sport_id = s.id
             WHERE v.verification_status = 'pending' AND v.deleted_at IS NULL
             GROUP BY v.id
             ORDER BY v.created_at ASC LIMIT 5"
        );

        // Recent bookings
        $recentBookings = $this->db->fetchAll(
            "SELECT b.booking_reference, b.booking_date, b.amount, b.status, b.payment_status,
                    v.name AS venue_name, u.name AS user_name
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             JOIN users u  ON b.user_id  = u.id
             ORDER BY b.created_at DESC LIMIT 5"
        );

        // Subscription plan distribution
        $planDist = $this->db->fetchAll(
            "SELECT p.name, COUNT(s.id) AS total
             FROM subscriptions s
             JOIN subscription_plans p ON s.plan_id = p.id
             WHERE s.status = 'active'
             GROUP BY p.id, p.name ORDER BY total DESC"
        );

        // NEW: Sport performance analytics
        $sportStats = $this->db->fetchAll(
            "SELECT 
                s.name,
                s.icon,
                COUNT(b.id) as total_bookings,
                SUM(CASE WHEN b.payment_status='paid' THEN b.amount ELSE 0 END) as revenue,
                COUNT(CASE WHEN b.booking_date >= DATE_FORMAT(NOW(),'%Y-%m-01') THEN 1 END) as bookings_this_month
             FROM sports s
             LEFT JOIN bookings b ON s.id = b.sport_id
             WHERE s.is_active = 1
             GROUP BY s.id, s.name, s.icon
             ORDER BY total_bookings DESC
             LIMIT 6"
        );

        // NEW: Top performing venues
        $topVenues = $this->db->fetchAll(
            "SELECT 
                v.id,
                v.name,
                v.city,
                COUNT(b.id) as total_bookings,
                SUM(CASE WHEN b.payment_status='paid' THEN b.amount ELSE 0 END) as revenue
             FROM venues v
             LEFT JOIN bookings b ON v.id = b.venue_id
             WHERE v.deleted_at IS NULL
             GROUP BY v.id, v.name, v.city
             ORDER BY revenue DESC
             LIMIT 5"
        );

        // NEW: Upcoming bookings (next 7 days)
        $upcomingBookings = $this->db->fetchAll(
            "SELECT 
                b.booking_reference,
                b.booking_date,
                b.start_time,
                v.name as venue_name,
                c.name as court_name,
                s.name as sport_name,
                u.name as user_name,
                u.whatsapp_number
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             JOIN courts c ON b.court_id = c.id
             JOIN sports s ON b.sport_id = s.id
             JOIN users u ON b.user_id = u.id
             WHERE b.booking_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             AND b.status = 'confirmed'
             ORDER BY b.booking_date ASC, b.start_time ASC
             LIMIT 8"
        );

        // NEW: Notifications - Pending actions
        $notifications = [];
        if ($stats['pending_venues'] > 0) {
            $notifications[] = [
                'type' => 'warning',
                'icon' => 'building-fill',
                'title' => 'Pending Venues',
                'message' => $stats['pending_venues'] . ' venue' . ($stats['pending_venues'] > 1 ? 's' : '') . ' awaiting approval',
                'link' => '/venues?status=pending',
                'time' => 'now'
            ];
        }
        
        // Check for expiring subscriptions (next 7 days)
        $expiringSubs = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM subscriptions 
             WHERE status='active' 
             AND expires_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
        );
        if ($expiringSubs > 0) {
            $notifications[] = [
                'type' => 'info',
                'icon' => 'credit-card',
                'title' => 'Expiring Subscriptions',
                'message' => $expiringSubs . ' subscription' . ($expiringSubs > 1 ? 's' : '') . ' expiring soon',
                'link' => '/subscriptions',
                'time' => '1h ago'
            ];
        }

        // Add booking notifications
        if ($stats['bookings_today'] > 0) {
            $notifications[] = [
                'type' => 'success',
                'icon' => 'calendar-check',
                'title' => 'New Bookings',
                'message' => $stats['bookings_today'] . ' booking' . ($stats['bookings_today'] > 1 ? 's' : '') . ' received today',
                'link' => '/bookings',
                'time' => 'today'
            ];
        }

        $this->render('dashboard.index', [
            'title'            => 'Dashboard',
            'stats'            => $stats,
            'roleStats'        => $roleStats,
            'recentRoleLogins' => $recentRoleLogins,
            'bookStats'        => $bookStats,
            'subStats'         => $subStats,
            'monthlyRev'       => $monthlyRev,
            'recentUsers'      => $recentUsers,
            'recentActivity'   => $recentActivity,
            'venueStats'       => $venueStats,
            'pendingVenues'    => $pendingVenues,
            'recentBookings'   => $recentBookings,
            'planDist'         => $planDist,
            'sportStats'       => $sportStats,
            'topVenues'        => $topVenues,
            'upcomingBookings' => $upcomingBookings,
            'notifications'    => $notifications,
        ]);
    }

    // ── Venue Owner ───────────────────────────────────────────────
    private function ownerDashboard(): void
    {
        $ownerId    = $this->user()['id'];
        $venueModel = new Venue();
        $subModel   = new Subscription();
        $actModel   = new ActivityLog();

        $myVenues = $venueModel->getByOwner($ownerId);
        $mySub    = $subModel->getActiveByUser($ownerId);

        $venueStats = [
            'total'    => count($myVenues),
            'active'   => count(array_filter($myVenues, fn($v) => $v['status'] === 'active')),
            'pending'  => count(array_filter($myVenues, fn($v) => $v['verification_status'] === 'pending')),
            'verified' => count(array_filter($myVenues, fn($v) => (int)$v['is_verified'] === 1)),
        ];

        // Booking stats for this owner's venues only
        $bookStats = $this->db->fetch(
            "SELECT
                COUNT(*)  AS total,
                SUM(" . \App\Models\Booking::pendingCondition('b') . ") AS pending,
                SUM(b.status = 'confirmed') AS confirmed,
                SUM(b.status = 'cancelled') AS cancelled,
                SUM(CASE WHEN b.payment_status = 'paid' THEN b.amount ELSE 0 END) AS total_revenue,
                SUM(CASE WHEN b.payment_status = 'paid'
                    AND b.booking_date >= DATE_FORMAT(NOW(),'%Y-%m-01')
                    THEN b.amount ELSE 0 END) AS monthly_revenue
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             WHERE v.owner_id = ? AND v.deleted_at IS NULL",
            [$ownerId]
        ) ?: [];

        // Recent bookings for owner's venues
        $recentBookings = $this->db->fetchAll(
            "SELECT b.booking_reference, b.booking_date, b.start_time, b.end_time,
                    b.amount, b.status, b.payment_status,
                    v.name AS venue_name, u.name AS user_name, u.phone AS user_phone
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             JOIN users u  ON b.user_id  = u.id
             WHERE v.owner_id = ?
             ORDER BY b.created_at DESC LIMIT 8",
            [$ownerId]
        );

        // Monthly revenue for owner (last 6 months) - fill all months
        $ownerMonthlyRevRaw = $this->db->fetchAll(
            "SELECT DATE_FORMAT(booking_date,'%Y-%m') AS month,
                    SUM(CASE WHEN payment_status='paid' THEN amount ELSE 0 END) AS revenue,
                    COUNT(*) AS bookings
             FROM bookings
             WHERE venue_id IN (SELECT id FROM venues WHERE owner_id = ?)
             AND booking_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month ASC",
            [$ownerId]
        );

        // Fill in missing months with zero values for accurate chart display
        $ownerMonthlyRev = [];
        $currentDate = new \DateTime();
        for ($i = 5; $i >= 0; $i--) {
            $date = clone $currentDate;
            $date->modify("-$i months");
            $monthKey = $date->format('Y-m');
            
            // Find matching data or use zero
            $found = false;
            foreach ($ownerMonthlyRevRaw as $row) {
                if ($row['month'] === $monthKey) {
                    $ownerMonthlyRev[] = [
                        'month' => $monthKey,
                        'revenue' => round((float)$row['revenue'], 2),
                        'bookings' => (int)$row['bookings']
                    ];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $ownerMonthlyRev[] = [
                    'month' => $monthKey,
                    'revenue' => 0.0,
                    'bookings' => 0
                ];
            }
        }

        $activity = $actModel->getRecentForUser($ownerId, 8);
        $allPlans = (new \App\Models\SubscriptionPlan())->getActivePlans();

        // Best Performing Venues (by revenue)
        $bestPerforming = $this->db->fetchAll(
            "SELECT 
                v.id,
                v.name,
                v.city,
                COALESCE(
                    (SELECT image_path FROM venue_images WHERE venue_id = v.id AND image_type = 'featured' LIMIT 1),
                    (SELECT image_path FROM venue_images WHERE venue_id = v.id ORDER BY id ASC LIMIT 1)
                ) as featured_image,
                COUNT(b.id) as total_bookings,
                SUM(CASE WHEN b.payment_status='paid' THEN b.amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN b.booking_date >= DATE_FORMAT(NOW(),'%Y-%m-01') AND b.payment_status='paid' THEN b.amount ELSE 0 END) as monthly_revenue
             FROM venues v
             LEFT JOIN bookings b ON v.id = b.venue_id
             WHERE v.owner_id = ? AND v.deleted_at IS NULL
             GROUP BY v.id, v.name, v.city
             HAVING total_revenue > 0
             ORDER BY total_revenue DESC
             LIMIT 3",
            [$ownerId]
        );

        // Most Loved Venues (by bookings count and rating)
        $mostLoved = $this->db->fetchAll(
            "SELECT 
                v.id,
                v.name,
                v.city,
                v.rating,
                v.total_reviews,
                COALESCE(
                    (SELECT image_path FROM venue_images WHERE venue_id = v.id AND image_type = 'featured' LIMIT 1),
                    (SELECT image_path FROM venue_images WHERE venue_id = v.id ORDER BY id ASC LIMIT 1)
                ) as featured_image,
                COUNT(b.id) as total_bookings,
                COUNT(CASE WHEN b.booking_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_bookings
             FROM venues v
             LEFT JOIN bookings b ON v.id = b.venue_id
             WHERE v.owner_id = ? AND v.deleted_at IS NULL
             GROUP BY v.id, v.name, v.city, v.rating, v.total_reviews
             HAVING total_bookings > 0
             ORDER BY v.rating DESC, total_bookings DESC
             LIMIT 3",
            [$ownerId]
        );

        $this->render('dashboard.owner', [
            'title'           => 'My Dashboard',
            'myVenues'        => $myVenues,
            'venueStats'      => $venueStats,
            'bookStats'       => $bookStats,
            'mySub'           => $mySub,
            'recentBookings'  => $recentBookings,
            'ownerMonthlyRev' => $ownerMonthlyRev,
            'activity'        => $activity,
            'bestPerforming'  => $bestPerforming,
            'mostLoved'       => $mostLoved,
            'allPlans'        => $allPlans,
            'recommendations' => (new OwnerRecommendationService())->forOwner($ownerId, [
                'myVenues'   => $myVenues,
                'mySub'      => $mySub,
                'bookStats'  => $bookStats,
                'venueStats' => $venueStats,
            ]),
        ]);
    }
}
