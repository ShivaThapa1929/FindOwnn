<?php
namespace Api\V1;

require_once __DIR__ . '/ApiController.php';

class ReviewController extends ApiController
{
    public static function handle($method, $id, $query, $body)
    {
        if ($method === 'POST') {
            self::requireAuth();
            return self::create($body);
        }
        
        if ($method === 'GET' && $id) {
            return self::show($id);
        }
        
        if ($method === 'GET') {
            return self::index($query);
        }
        
        return self::error('Method not allowed', 405);
    }
    
    /**
     * Get reviews (with filters)
     */
    private static function index($query)
    {
        // Placeholder - implement when reviews table exists
        return self::success([
            'reviews' => [],
            'meta' => [
                'current_page' => 1,
                'total' => 0,
                'per_page' => 20
            ]
        ]);
    }
    
    /**
     * Get single review
     */
    private static function show($id)
    {
        // Placeholder
        return self::error('Review not found', 404, 'REVIEW_001');
    }
    
    /**
     * Create review
     */
    private static function create($data)
    {
        // Validate
        if (empty($data['booking_id']) || empty($data['rating'])) {
            return self::error('Booking ID and rating required', 400);
        }
        
        // Check if booking exists and belongs to user
        $booking = self::$db->fetch(
            "SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status = 'completed'",
            [$data['booking_id'], self::$user['id']]
        );
        
        if (!$booking) {
            return self::error('Booking not found or not completed', 404);
        }
        
        // Placeholder - implement when reviews table exists
        return self::success([
            'message' => 'Review submitted successfully'
        ], 'Review submitted successfully', 201);
    }
}
