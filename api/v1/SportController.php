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
        $sql = "SELECT s.*,
                (SELECT COUNT(DISTINCT v.id) FROM venues v
                 JOIN venue_sports vs ON v.id = vs.venue_id
                 WHERE vs.sport_id = s.id AND v.status = 'active' AND v.deleted_at IS NULL) as total_venues,
                (SELECT COUNT(*) FROM courts c
                 WHERE c.sport_id = s.id AND c.status = 'active' AND c.deleted_at IS NULL) as total_courts
                FROM sports s
                WHERE s.is_active = 1";
        
        if (!empty($query['featured'])) {
            $sql .= " AND s.is_featured = 1";
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
