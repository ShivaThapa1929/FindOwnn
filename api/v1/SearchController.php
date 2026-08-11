<?php
namespace Api\V1;

require_once __DIR__ . '/ApiController.php';

class SearchController extends ApiController
{
    public static function handle($query)
    {
        $searchTerm = $query['q'] ?? '';
        
        if (empty($searchTerm)) {
            return self::error('Search term required', 400, 'VALIDATION_ERROR');
        }
        
        $searchTerm = '%' . $searchTerm . '%';
        
        // Search venues
        $venues = self::$db->fetchAll(
            "SELECT id, name, city, state, price_per_hour, rating, 'venue' as type
             FROM venues
             WHERE (name LIKE ? OR city LIKE ? OR address LIKE ?)
             AND status = 'active' AND deleted_at IS NULL
             LIMIT 10",
            [$searchTerm, $searchTerm, $searchTerm]
        );
        
        // Search courts
        $courts = self::$db->fetchAll(
            "SELECT c.id, c.name, v.name as venue_name, v.city, c.price_per_hour, 'court' as type
             FROM courts c
             LEFT JOIN venues v ON c.venue_id = v.id
             WHERE (c.name LIKE ? OR c.description LIKE ?)
             AND c.status = 'active' AND c.deleted_at IS NULL
             LIMIT 10",
            [$searchTerm, $searchTerm]
        );
        
        // Search sports
        $sports = self::$db->fetchAll(
            "SELECT id, name, slug, icon, 'sport' as type
             FROM sports
             WHERE name LIKE ? AND is_active = 1
             LIMIT 5",
            [$searchTerm]
        );
        
        $results = [
            'venues' => $venues,
            'courts' => $courts,
            'sports' => $sports,
            'total' => count($venues) + count($courts) + count($sports)
        ];
        
        return self::success($results);
    }
}
