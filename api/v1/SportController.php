<?php
namespace Api\V1;

require_once __DIR__ . '/ApiController.php';

class SportController extends ApiController
{
    public static function handle($method, $id, $query)
    {
        if ($method !== 'GET') {
            return self::error('Method not allowed', 405);
        }
        
        if ($id) {
            return self::show($id);
        }
        
        return self::index($query);
    }
    
    /**
     * Get all active sports
     */
    private static function index($query)
    {
        // Single aggregated query — O(S) rows vs O(S×V) correlated subqueries
        $sql = "SELECT s.*,
                COALESCE(vc.total_venues, 0) AS total_venues,
                COALESCE(cc.total_courts, 0) AS total_courts
                FROM sports s
                LEFT JOIN (
                    SELECT vs.sport_id, COUNT(DISTINCT v.id) AS total_venues
                    FROM venue_sports vs
                    INNER JOIN venues v ON v.id = vs.venue_id
                        AND v.status = 'active' AND v.deleted_at IS NULL
                    GROUP BY vs.sport_id
                ) vc ON vc.sport_id = s.id
                LEFT JOIN (
                    SELECT sport_id, COUNT(*) AS total_courts
                    FROM courts
                    WHERE status = 'active' AND deleted_at IS NULL
                    GROUP BY sport_id
                ) cc ON cc.sport_id = s.id
                WHERE s.is_active = 1";

        if (!empty($query['featured'])) {
            $sql .= " AND s.is_featured = 1";
        }

        if (!empty($query['live'])) {
            $sql .= " AND COALESCE(vc.total_venues, 0) > 0";
        }

        $sql .= " ORDER BY s.sort_order ASC, s.name ASC";

        $sports = self::$db->fetchAll($sql);
        
        return self::success([
            'sports' => array_map(function($sport) {
                return [
                    'id' => (int) $sport['id'],
                    'name' => $sport['name'],
                    'slug' => $sport['slug'],
                    'description' => $sport['description'],
                    'icon' => $sport['icon'] ?? null,
                    'image' => $sport['image'] ?? null,
                    'color' => $sport['color'] ?? null,
                    'is_featured' => (bool) ($sport['is_featured'] ?? false),
                    'total_venues' => (int) $sport['total_venues'],
                    'total_courts' => (int) $sport['total_courts'],
                    'sort_order' => (int) ($sport['sort_order'] ?? 0)
                ];
            }, $sports)
        ]);
    }
    
    /**
     * Get sport details
     */
    private static function show($id)
    {
        $sport = self::$db->fetch(
            "SELECT * FROM sports WHERE id = ? AND is_active = 1",
            [$id]
        );
        
        if (!$sport) {
            return self::error('Sport not found', 404, 'SPORT_001');
        }
        
        // Get venues offering this sport
        $venues = self::$db->fetchAll(
            "SELECT v.id, v.name, v.city, v.state, v.price_per_hour, v.rating
             FROM venues v
             JOIN venue_sports vs ON v.id = vs.venue_id
             WHERE vs.sport_id = ? AND v.status = 'active' AND v.deleted_at IS NULL
             LIMIT 10",
            [$id]
        );
        
        return self::success([
            'id' => (int) $sport['id'],
            'name' => $sport['name'],
            'slug' => $sport['slug'],
            'description' => $sport['description'],
            'icon' => $sport['icon'] ?? null,
            'image' => $sport['image'] ?? null,
            'color' => $sport['color'] ?? null,
            'is_featured' => (bool) ($sport['is_featured'] ?? false),
            'popular_venues' => array_map(function($venue) {
                return [
                    'id' => (int) $venue['id'],
                    'name' => $venue['name'],
                    'city' => $venue['city'],
                    'state' => $venue['state'],
                    'price_per_hour' => (int) $venue['price_per_hour'],
                    'rating' => (float) $venue['rating']
                ];
            }, $venues)
        ]);
    }
}
