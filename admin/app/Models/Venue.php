<?php

namespace App\Models;

use App\Core\Model;

class Venue extends Model
{
    protected string $table    = 'venues';
    protected array  $fillable = [
        'owner_id', 'name', 'slug', 'description',
        'address', 'city', 'state', 'pincode', 'google_map_link',
        'amenities', 'status', 'verification_status', 'is_verified',
        'verified_by', 'verified_at', 'badge_expires_at', 'verification_notes',
        'price_per_hour', 'featured_image', 'rating', 'total_reviews',
        'contact_person', 'contact_email', 'contact_phone', 'whatsapp_number',
        'opening_time', 'closing_time', 'booking_advance_days', 'cancellation_hours',
        'latitude', 'longitude',
        'created_at', 'updated_at', 'deleted_at',
    ];

    public function getAllWithOwner(
        int $page = 1, 
        int $perPage = 20, 
        string $filter = 'all', 
        string $search = '',
        string $city = '',
        string $verified = '',
        string $sortBy = 'newest',
        int $ownerId = null
    ): array {
        $where  = 'v.deleted_at IS NULL';
        $params = [];

        // Owner filter
        if ($ownerId !== null) {
            $where  .= ' AND v.owner_id = ?';
            $params[] = $ownerId;
        }

        // Status filter
        if ($filter !== 'all') {
            $where  .= ' AND v.verification_status = ?';
            $params[] = $filter;
        }

        // Search filter
        if ($search !== '') {
            $like    = "%{$search}%";
            $where  .= ' AND (v.name LIKE ? OR v.city LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
            $params = array_merge($params, [$like, $like, $like, $like]);
        }

        // City filter
        if ($city !== '') {
            $where  .= ' AND v.city = ?';
            $params[] = $city;
        }

        // Verified badge filter
        if ($verified === 'yes') {
            $where .= ' AND v.is_verified = 1';
        } elseif ($verified === 'no') {
            $where .= ' AND v.is_verified = 0';
        }

        // Sorting
        $orderBy = match($sortBy) {
            'newest'     => 'v.created_at DESC',
            'oldest'     => 'v.created_at ASC',
            'name_asc'   => 'v.name ASC',
            'name_desc'  => 'v.name DESC',
            'price_low'  => 'v.price_per_hour ASC',
            'price_high' => 'v.price_per_hour DESC',
            'city'       => 'v.city ASC, v.name ASC',
            default      => 'v.created_at DESC',
        };

        $total  = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM venues v LEFT JOIN users u ON v.owner_id = u.id WHERE {$where}", $params
        );
        $offset = ($page - 1) * $perPage;
        $pages  = (int) ceil($total / $perPage);

        $data = $this->db->fetchAll(
            "SELECT v.*, u.name AS owner_name, u.email AS owner_email,
                    GROUP_CONCAT(s.name SEPARATOR ', ') AS sports
             FROM venues v
             LEFT JOIN users u ON v.owner_id = u.id
             LEFT JOIN venue_sports vs ON v.id = vs.venue_id
             LEFT JOIN sports s ON vs.sport_id = s.id
             WHERE {$where}
             GROUP BY v.id
             ORDER BY {$orderBy}
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return compact('data', 'total', 'page', 'perPage', 'pages');
    }

    public function getWithOwner(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT v.*, u.name AS owner_name, u.email AS owner_email, u.phone AS owner_phone
             FROM venues v
             LEFT JOIN users u ON v.owner_id = u.id
             WHERE v.id = ?",
            [$id]
        );
    }

    public function getByOwner(int $ownerId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM venues WHERE owner_id = ? AND deleted_at IS NULL ORDER BY created_at DESC",
            [$ownerId]
        );
    }

    public function approve(int $id, int $adminId): void
    {
        $this->update($id, [
            'verification_status' => 'approved',
            'status'              => 'active',
            'verified_by'         => $adminId,
            'verified_at'         => now(),
        ]);
    }

    public function reject(int $id, int $adminId, string $notes = ''): void
    {
        $this->update($id, [
            'verification_status' => 'rejected',
            'status'              => 'inactive',
            'verified_by'         => $adminId,
            'verification_notes'  => $notes,
        ]);
    }

    public function assignBadge(int $id, int $adminId, string $expiryDate, string $notes = ''): void
    {
        $this->update($id, [
            'is_verified'        => 1,
            'verified_by'        => $adminId,
            'badge_expires_at'   => $expiryDate,
            'verification_notes' => $notes,
        ]);
    }

    public function removeBadge(int $id): void
    {
        $this->update($id, [
            'is_verified'      => 0,
            'badge_expires_at' => null,
        ]);
    }

    public function getStats(): array
    {
        return [
            'total'    => $this->count('deleted_at IS NULL'),
            'approved' => $this->count("deleted_at IS NULL AND verification_status = 'approved'"),
            'pending'  => $this->count("deleted_at IS NULL AND verification_status = 'pending'"),
            'rejected' => $this->count("deleted_at IS NULL AND verification_status = 'rejected'"),
            'verified' => $this->count('deleted_at IS NULL AND is_verified = 1'),
        ];
    }

    public function getImages(int $venueId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM venue_images WHERE venue_id = ? ORDER BY sort_order ASC",
            [$venueId]
        );
    }
}
