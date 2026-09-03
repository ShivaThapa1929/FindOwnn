<?php
/**
 * Mock API - For testing without database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Mock data
$mockVenues = [
    [
        'id' => 1,
        'name' => 'Bhuj Box Arena',
        'city' => 'Bhuj',
        'address' => 'Sanskar Nagar, Bhuj',
        'price_per_hour' => 1000,
        'rating' => 4.9,
        'total_reviews' => 125,
        'sports' => 'Box Cricket',
        'featured_image' => 'assets/images/venue1.jpg',
        'has_floodlights' => true,
        'has_parking' => true,
        'has_water' => true,
        'available_courts' => 2,
        'total_courts' => 3
    ],
    [
        'id' => 2,
        'name' => 'Champion Turf',
        'city' => 'Bhuj',
        'address' => 'Madhapar Road, Bhuj',
        'price_per_hour' => 800,
        'rating' => 4.8,
        'total_reviews' => 98,
        'sports' => 'Pickleball',
        'featured_image' => 'assets/images/venue2.jpg',
        'has_floodlights' => false,
        'has_parking' => true,
        'has_water' => true,
        'available_courts' => 1,
        'total_courts' => 2
    ],
    [
        'id' => 3,
        'name' => 'Kutch Sports Hub',
        'city' => 'Bhuj',
        'address' => 'Mundra Road, Bhuj',
        'price_per_hour' => 1200,
        'rating' => 4.7,
        'total_reviews' => 87,
        'sports' => 'Box Cricket',
        'featured_image' => 'assets/images/venue3.jpg',
        'has_floodlights' => true,
        'has_parking' => true,
        'has_water' => true,
        'available_courts' => 3,
        'total_courts' => 4
    ]
];

$mockSports = [
    ['id' => 1, 'name' => 'Box Cricket', 'slug' => 'cricket'],
    ['id' => 2, 'name' => 'Pickleball', 'slug' => 'pickleball']
];

// Parse request
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api/v1/mock.php', '', $path);
parse_str($_SERVER['QUERY_STRING'] ?? '', $query);

// Get resource
$resource = $query['resource'] ?? 'venues';

// Route
switch ($resource) {
    case 'venues':
        // Apply filters
        $filtered = $mockVenues;
        
        if (!empty($query['sport'])) {
            $filtered = array_filter($filtered, function($v) use ($query) {
                return stripos($v['sports'], $query['sport']) !== false;
            });
        }
        
        if (!empty($query['city'])) {
            $filtered = array_filter($filtered, function($v) use ($query) {
                return $v['city'] === $query['city'];
            });
        }
        
        if (!empty($query['search'])) {
            $filtered = array_filter($filtered, function($v) use ($query) {
                return stripos($v['name'], $query['search']) !== false ||
                       stripos($v['address'], $query['search']) !== false;
            });
        }
        
        $filtered = array_values($filtered);
        
        echo json_encode([
            'success' => true,
            'message' => 'Venues retrieved successfully',
            'data' => [
                'venues' => $filtered,
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 12,
                    'total_pages' => 1,
                    'total_items' => count($filtered)
                ]
            ]
        ], JSON_PRETTY_PRINT);
        break;
        
    case 'sports':
        echo json_encode([
            'success' => true,
            'message' => 'Sports retrieved successfully',
            'data' => $mockSports
        ], JSON_PRETTY_PRINT);
        break;
        
    case 'cities':
        echo json_encode([
            'success' => true,
            'message' => 'Cities retrieved successfully',
            'data' => ['Bhuj', 'Anjar', 'Mundra', 'Gandhidham']
        ], JSON_PRETTY_PRINT);
        break;
        
    case 'search':
        $query_str = $query['q'] ?? '';
        $results = array_filter($mockVenues, function($v) use ($query_str) {
            return stripos($v['name'], $query_str) !== false;
        });
        
        echo json_encode([
            'success' => true,
            'message' => 'Search results',
            'data' => array_values($results)
        ], JSON_PRETTY_PRINT);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Resource not found',
            'code' => 'NOT_FOUND'
        ], JSON_PRETTY_PRINT);
}
