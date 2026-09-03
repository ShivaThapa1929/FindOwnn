<?php

namespace App\Models;

use App\Core\Model;

class Booking extends Model
{
    protected string $table    = 'bookings';
    protected array  $fillable = [
        'venue_id', 'user_id', 'booking_date', 'start_time', 'end_time',
        'total_hours', 'amount', 'status', 'payment_status', 'payment_id',
        'booking_reference', 'notes', 'created_at', 'updated_at',
    ];

    /** SQL fragment: unpaid / awaiting confirmation bookings */
    public static function pendingCondition(string $alias = 'b'): string
    {
        $a = $alias;
        return "({$a}.status = 'pending' OR ({$a}.payment_status = 'pending' AND {$a}.status NOT IN ('cancelled','completed')))";
    }

    /** Append status filter to WHERE clause and params */
    public static function applyStatusFilter(string $filter, string &$where, array &$params, string $alias = 'b'): void
    {
        if ($filter === 'all') {
            return;
        }
        if ($filter === 'pending') {
            $where .= ' AND ' . self::pendingCondition($alias);
            return;
        }
        $where .= " AND {$alias}.status = ?";
        $params[] = $filter;
    }

    public function getAllWithDetails(int $page = 1, int $perPage = 20, string $filter = 'all', string $search = ''): array
    {
        $where  = '1=1';
        $params = [];

        self::applyStatusFilter($filter, $where, $params);

        if ($search !== '') {
            $like    = "%{$search}%";
            $where  .= ' AND (b.booking_reference LIKE ? OR v.name LIKE ? OR u.name LIKE ?)';
            $params  = array_merge($params, [$like, $like, $like]);
        }

        $total  = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM bookings b
             LEFT JOIN venues v ON b.venue_id = v.id
             LEFT JOIN users u ON b.user_id = u.id
             WHERE {$where}",
            $params
        );
        $offset = ($page - 1) * $perPage;
        $pages  = (int) ceil($total / $perPage);

        $data = $this->db->fetchAll(
            "SELECT b.*, 
                    v.name AS venue_name, v.address AS venue_address,
                    c.name AS court_name, c.court_number,
                    s.name AS sport_name, s.slug AS sport_slug,
                    u.name AS user_name, u.email AS user_email, u.phone AS user_phone, u.whatsapp_number
             FROM bookings b
             LEFT JOIN venues v ON b.venue_id = v.id
             LEFT JOIN courts c ON b.court_id = c.id
             LEFT JOIN sports s ON b.sport_id = s.id
             LEFT JOIN users u ON b.user_id = u.id
             WHERE {$where}
             ORDER BY b.booking_date DESC, b.start_time DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return compact('data', 'total', 'page', 'perPage', 'pages');
    }

    public function getByVenue(int $venueId, int $page = 1, int $perPage = 20): array
    {
        return $this->paginate($page, $perPage, 'venue_id = ?', [$venueId], 'created_at DESC');
    }

    public function getStats(): array
    {
        return $this->db->fetch(
            "SELECT
                COUNT(*) AS total,
                SUM(" . self::pendingCondition('b') . ") AS pending,
                SUM(b.status = 'confirmed') AS confirmed,
                SUM(b.status = 'cancelled') AS cancelled,
                SUM(b.payment_status = 'paid') AS paid_count,
                SUM(CASE WHEN b.payment_status = 'paid' THEN b.amount ELSE 0 END) AS total_revenue
             FROM bookings b"
        ) ?: [];
    }

    /** Booking KPIs scoped to one venue owner's venues. */
    public function getStatsForOwner(int $ownerId): array
    {
        return $this->db->fetch(
            "SELECT
                COUNT(*) AS total,
                SUM(" . self::pendingCondition('b') . ") AS pending,
                SUM(b.status = 'confirmed') AS confirmed,
                SUM(b.status = 'cancelled') AS cancelled,
                SUM(b.payment_status = 'paid') AS paid_count,
                SUM(CASE WHEN b.payment_status = 'paid' THEN b.amount ELSE 0 END) AS total_revenue
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             WHERE v.owner_id = ? AND v.deleted_at IS NULL",
            [$ownerId]
        ) ?: [
            'total' => 0, 'pending' => 0, 'confirmed' => 0, 'cancelled' => 0,
            'paid_count' => 0, 'total_revenue' => 0,
        ];
    }

    public function getMonthlyRevenue(int $months = 12): array
    {
        $rawData = $this->db->fetchAll(
            "SELECT DATE_FORMAT(booking_date, '%Y-%m') AS month,
                    COUNT(*) AS total_bookings,
                    SUM(CASE WHEN payment_status = 'paid' THEN amount ELSE 0 END) AS revenue
             FROM bookings
             WHERE booking_date >= DATE_SUB(NOW(), INTERVAL ? MONTH)
             GROUP BY month
             ORDER BY month ASC",
            [$months]
        );

        // Fill in missing months with zero values
        $result = [];
        $currentDate = new \DateTime();
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = clone $currentDate;
            $date->modify("-$i months");
            $monthKey = $date->format('Y-m');
            
            // Find matching data or use zero
            $found = false;
            foreach ($rawData as $row) {
                if ($row['month'] === $monthKey) {
                    $result[] = [
                        'month' => $monthKey,
                        'total_bookings' => (int)$row['total_bookings'],
                        'revenue' => (float)$row['revenue']
                    ];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $result[] = [
                    'month' => $monthKey,
                    'total_bookings' => 0,
                    'revenue' => 0.0
                ];
            }
        }
        
        return $result;
    }

    public function generateReference(): string
    {
        return 'BK-' . strtoupper(substr(uniqid(), -8));
    }
}
