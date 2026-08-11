<?php
namespace Api\V1;

require_once __DIR__ . '/ApiController.php';

class LocationController extends ApiController
{
    public static function handleCities($query)
    {
        // Check if DB table cities exists
        $dbCities = self::$db->fetchAll(
            "SELECT c.name as city, s.name as state, c.is_default 
             FROM cities c 
             JOIN states s ON c.state_id = s.id 
             WHERE c.status = 'active'
             ORDER BY c.is_default DESC, c.name ASC"
        );
        
        if (!empty($dbCities)) {
            return self::success([
                'cities' => array_map(function($city) {
                    return [
                        'city'       => $city['city'],
                        'state'      => $city['state'],
                        'is_default' => (bool) $city['is_default']
                    ];
                }, $dbCities)
            ]);
        }

        $sql = "SELECT DISTINCT city, state, COUNT(*) as venue_count
                FROM venues
                WHERE status = 'active' AND deleted_at IS NULL";
        
        $params = [];
        
        if (!empty($query['state'])) {
            $sql .= " AND state = ?";
            $params[] = $query['state'];
        }
        
        if (!empty($query['search'])) {
            $sql .= " AND city LIKE ?";
            $params[] = '%' . $query['search'] . '%';
        }
        
        $sql .= " GROUP BY city, state ORDER BY venue_count DESC, city ASC";
        
        $cities = self::$db->fetchAll($sql, $params);
        
        return self::success([
            'cities' => array_map(function($city) {
                return [
                    'city' => $city['city'],
                    'state' => $city['state'],
                    'venue_count' => (int) $city['venue_count']
                ];
            }, $cities)
        ]);
    }
    
    public static function handleStates()
    {
        $dbStates = self::$db->fetchAll("SELECT name as state, code FROM states WHERE status = 'active' ORDER BY name ASC");
        
        if (!empty($dbStates)) {
            return self::success(['states' => $dbStates]);
        }

        $states = self::$db->fetchAll(
            "SELECT DISTINCT state, COUNT(DISTINCT city) as city_count, COUNT(*) as venue_count
             FROM venues
             WHERE status = 'active' AND deleted_at IS NULL
             GROUP BY state
             ORDER BY venue_count DESC"
        );
        
        return self::success([
            'states' => array_map(function($state) {
                return [
                    'state' => $state['state'],
                    'city_count' => (int) $state['city_count'],
                    'venue_count' => (int) $state['venue_count']
                ];
            }, $states)
        ]);
    }
}
