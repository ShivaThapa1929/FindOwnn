<?php

namespace App\Models;

use App\Core\Model;
use App\Helpers\EmailHelper;

class User extends Model
{
    protected string $table      = 'users';
    protected array  $fillable   = [
        'name', 'email', 'password', 'phone', 'phone_verified_at', 'role',
        'status', 'avatar', 'email_verified_at', 'last_login_at',
        'created_at', 'updated_at',
    ];
    protected array $hidden = ['password'];

    public static function normalizeEmail(string $email): string
    {
        return EmailHelper::normalize($email);
    }

    public function findByEmail(string $email): array|false
    {
        $email = self::normalizeEmail($email);
        if ($email === '') {
            return false;
        }

        $user = $this->db->fetch(
            "SELECT * FROM {$this->table}
             WHERE LOWER(TRIM(email)) = ?
               AND deleted_at IS NULL
             LIMIT 1",
            [$email]
        );

        if ($user) {
            return $user;
        }

        $gmailLocal = EmailHelper::gmailLocalKey($email);
        if ($gmailLocal === null) {
            return false;
        }

        return $this->db->fetch(
            "SELECT * FROM {$this->table}
             WHERE deleted_at IS NULL
               AND LOWER(TRIM(SUBSTRING_INDEX(email, '@', -1))) IN ('gmail.com', 'googlemail.com')
               AND REPLACE(
                     SUBSTRING_INDEX(SUBSTRING_INDEX(LOWER(TRIM(email)), '@', 1), '+', 1),
                     '.', ''
                   ) = ?
             LIMIT 1",
            [$gmailLocal]
        ) ?: false;
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function updateLastLogin(int $id): void
    {
        $this->db->execute(
            "UPDATE users SET last_login_at = ? WHERE id = ?",
            [now(), $id]
        );
    }

    public function getByRole(string $role, int $page = 1, int $perPage = 20): array
    {
        return $this->paginate($page, $perPage, 'role = ? AND deleted_at IS NULL', [$role], 'id DESC');
    }

    public function countByRole(): array
    {
        return $this->db->fetchAll(
            "SELECT role, COUNT(*) as total FROM users WHERE deleted_at IS NULL GROUP BY role"
        );
    }

    public function search(string $query, int $page = 1, int $perPage = 20): array
    {
        $like = "%{$query}%";
        return $this->paginate(
            $page, $perPage,
            'deleted_at IS NULL AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)',
            [$like, $like, $like]
        );
    }

    public function toggleStatus(int $id): void
    {
        $user = $this->find($id);
        if ($user) {
            $new = $user['status'] === 'active' ? 'inactive' : 'active';
            $this->update($id, ['status' => $new]);
        }
    }

    public function getRecentUsers(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM users WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }

    /** Players = registered app users + walk-in customers */
    public function isPlayerRecord(array $user): bool
    {
        return $user['role'] === 'player'
            || str_contains($user['email'] ?? '', '@offline.findownn');
    }

    public function countPlayers(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users
             WHERE deleted_at IS NULL
               AND (role = 'player' OR email LIKE '%@offline.findownn')"
        );
    }

    public function getPlayers(int $page = 1, int $perPage = 20, string $search = '', string $filter = 'all', ?int $ownerId = null): array
    {
        $where  = "u.deleted_at IS NULL AND (u.role = 'player' OR u.email LIKE '%@offline.findownn')";
        $params = [];

        if ($ownerId) {
            $where .= " AND EXISTS (
                SELECT 1 FROM bookings b
                JOIN venues v ON b.venue_id = v.id
                WHERE b.user_id = u.id AND v.owner_id = ?
            )";
            $params[] = $ownerId;
        }

        if ($search !== '') {
            $like    = "%{$search}%";
            $where  .= ' AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.whatsapp_number LIKE ?)';
            $params  = array_merge($params, [$like, $like, $like, $like]);
        }

        if ($filter === 'active') {
            $where .= " AND u.status = 'active'";
        } elseif ($filter === 'walkin') {
            $where .= " AND u.email LIKE '%@offline.findownn'";
        } elseif ($filter === 'registered') {
            $where .= " AND u.role = 'player' AND u.email NOT LIKE '%@offline.findownn'";
        }

        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users u WHERE {$where}",
            $params
        );

        $offset = ($page - 1) * $perPage;
        $pages  = max(1, (int) ceil($total / $perPage));

        $bookingJoinParams = [];
        $bookingJoinExtra  = '';
        if ($ownerId) {
            $bookingJoinExtra  = ' AND b.venue_id IN (SELECT id FROM venues WHERE owner_id = ? AND deleted_at IS NULL)';
            $bookingJoinParams[] = $ownerId;
        }

        $dataParams = array_merge($bookingJoinParams, $params);

        $data = $this->db->fetchAll(
            "SELECT u.*,
                    COUNT(DISTINCT b.id) AS total_bookings,
                    COALESCE(SUM(CASE WHEN b.payment_status = 'paid' THEN b.amount ELSE 0 END), 0) AS total_spent,
                    MAX(b.booking_date) AS last_booking_date,
                    SUM(CASE WHEN b.status IN ('confirmed','pending') AND b.booking_date >= CURDATE() THEN 1 ELSE 0 END) AS upcoming_bookings
             FROM users u
             LEFT JOIN bookings b ON b.user_id = u.id{$bookingJoinExtra}
             WHERE {$where}
             GROUP BY u.id
             ORDER BY last_booking_date DESC, u.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $dataParams
        );

        return compact('data', 'total', 'page', 'perPage', 'pages');
    }

    public function findPlayer(int $id): array|false
    {
        $user = $this->find($id);
        if (!$user || !$this->isPlayerRecord($user)) {
            return false;
        }
        return $user;
    }

    public function playerBelongsToOwner(int $playerId, int $ownerId): bool
    {
        return (bool) $this->db->fetchColumn(
            "SELECT 1 FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             WHERE b.user_id = ? AND v.owner_id = ?
             LIMIT 1",
            [$playerId, $ownerId]
        );
    }

    public function getPlayerStats(int $playerId, ?int $ownerId = null): array
    {
        $venueClause = $ownerId ? ' AND v.owner_id = ?' : '';
        $params      = [$playerId];
        if ($ownerId) {
            $params[] = $ownerId;
        }

        return $this->db->fetch(
            "SELECT
                COUNT(b.id) AS total_bookings,
                COALESCE(SUM(CASE WHEN b.payment_status = 'paid' THEN b.amount ELSE 0 END), 0) AS total_spent,
                SUM(CASE WHEN b.status IN ('confirmed','pending') AND b.booking_date >= CURDATE() THEN 1 ELSE 0 END) AS upcoming,
                SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
                MAX(b.booking_date) AS last_booking_date
             FROM bookings b
             LEFT JOIN venues v ON b.venue_id = v.id
             WHERE b.user_id = ?{$venueClause}",
            $params
        ) ?: [];
    }

    public function getPlayerBookings(int $playerId, int $limit = 50, ?int $ownerId = null): array
    {
        $venueClause = $ownerId ? ' AND v.owner_id = ?' : '';
        $params      = [$playerId];
        if ($ownerId) {
            $params[] = $ownerId;
        }
        $params[] = $limit;

        return $this->db->fetchAll(
            "SELECT b.*,
                    v.name AS venue_name, v.city AS venue_city,
                    s.name AS sport_name,
                    c.name AS court_name
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             LEFT JOIN sports s ON b.sport_id = s.id
             LEFT JOIN courts c ON b.court_id = c.id
             WHERE b.user_id = ?{$venueClause}
             ORDER BY b.booking_date DESC, b.start_time DESC
             LIMIT ?",
            $params
        );
    }

    public function getPlayerUpcomingBooking(int $playerId, ?int $ownerId = null): array|false
    {
        $venueClause = $ownerId ? ' AND v.owner_id = ?' : '';
        $params      = [$playerId];
        if ($ownerId) {
            $params[] = $ownerId;
        }

        return $this->db->fetch(
            "SELECT b.*
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             WHERE b.user_id = ?
               AND b.status IN ('confirmed', 'pending')
               AND b.booking_date >= CURDATE()
               {$venueClause}
             ORDER BY b.booking_date ASC, b.start_time ASC
             LIMIT 1",
            $params
        );
    }

    public function getPlayerWhatsAppHistory(?string $phone, ?string $whatsapp): array
    {
        $numbers = array_filter(array_unique([
            preg_replace('/\D/', '', $phone ?? ''),
            preg_replace('/\D/', '', $whatsapp ?? ''),
        ]));

        if (empty($numbers)) {
            return [];
        }

        $phoneColumn = $this->whatsappPhoneColumn();

        $conditions = [];
        $params     = [];
        foreach ($numbers as $num) {
            if (strlen($num) >= 10) {
                $conditions[] = "REPLACE(REPLACE({$phoneColumn}, '+', ''), ' ', '') LIKE ?";
                $params[]     = '%' . substr($num, -10);
            }
        }

        if (empty($conditions)) {
            return [];
        }

        $where = implode(' OR ', $conditions);

        return $this->db->fetchAll(
            "SELECT * FROM whatsapp_messages
             WHERE {$where}
             ORDER BY created_at DESC
             LIMIT 10",
            $params
        );
    }

    private function whatsappPhoneColumn(): string
    {
        static $column = null;

        if ($column !== null) {
            return $column;
        }

        try {
            $columns = $this->db->fetchAll('SHOW COLUMNS FROM whatsapp_messages');
            $names   = array_column($columns, 'Field');
            $column  = in_array('recipient_number', $names, true) ? 'recipient_number' : 'phone_number';
        } catch (\Throwable) {
            $column = 'phone_number';
        }

        return $column;
    }
}
