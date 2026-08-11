<?php
namespace Api\V1;

require_once __DIR__ . '/ApiController.php';

class CourtController extends ApiController
{
    public static function handle($method, $id, $action, $query, $body)
    {
        if ($id && $action === 'images') {
            return $method === 'GET' ? self::getImages($id) : self::error('Method not allowed', 405);
        }
        
        if ($id && $action === 'availability') {
            return $method === 'GET' ? self::getAvailability($id, $query) : self::error('Method not allowed', 405);
        }
        
        if ($id && $action === 'reviews') {
            return $method === 'GET' ? self::getReviews($id, $query) : self::error('Method not allowed', 405);
        }
        
        if ($id && !$action) {
            return $method === 'GET' ? self::show($id) : self::error('Method not allowed', 405);
        }
        
        return $method === 'GET' ? self::index($query) : self::error('Method not allowed', 405);
    }
    
    /**
     * Get all courts with filters
     */
    private static function index($query)
    {
        $page = (int) ($query['page'] ?? 1);
        $perPage = min((int) ($query['per_page'] ?? 20), 50);
        
        $sql = "SELECT c.*, 
                v.name as venue_name, v.city, v.state, v.address,
                s.name as sport_name, s.icon as sport_icon,
                (SELECT image_path FROM court_images WHERE court_id = c.id ORDER BY (image_type = 'featured') DESC, sort_order ASC, id ASC LIMIT 1) as featured_image
                FROM courts c
                LEFT JOIN venues v ON c.venue_id = v.id
                LEFT JOIN sports s ON c.sport_id = s.id
                WHERE c.deleted_at IS NULL AND c.status = 'active'
                AND v.status = 'active' AND v.deleted_at IS NULL";
        
        $params = [];
        
        // Apply filters
        if (!empty($query['venue_id'])) {
            $sql .= " AND c.venue_id = ?";
            $params[] = $query['venue_id'];
        }
        
        if (!empty($query['sport'])) {
            $sql .= " AND s.slug = ?";
            $params[] = $query['sport'];
        }
        
        if (!empty($query['city'])) {
            $sql .= " AND v.city = ?";
            $params[] = $query['city'];
        }
        
        if (!empty($query['is_indoor'])) {
            $sql .= " AND c.is_indoor = ?";
            $params[] = $query['is_indoor'] === 'true' ? 1 : 0;
        }
        
        if (!empty($query['has_lighting'])) {
            $sql .= " AND c.has_lighting = ?";
            $params[] = $query['has_lighting'] === 'true' ? 1 : 0;
        }
        
        if (!empty($query['min_price'])) {
            $sql .= " AND c.price_per_hour >= ?";
            $params[] = $query['min_price'];
        }
        
        if (!empty($query['max_price'])) {
            $sql .= " AND c.price_per_hour <= ?";
            $params[] = $query['max_price'];
        }
        
        if (!empty($query['surface_type'])) {
            $sql .= " AND c.surface_type = ?";
            $params[] = $query['surface_type'];
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        $result = self::paginate($sql, $params, $page, $perPage);
        
        $courts = array_map(function($court) {
            return [
                'id' => (int) $court['id'],
                'venue_id' => (int) $court['venue_id'],
                'venue_name' => $court['venue_name'],
                'name' => $court['name'],
                'court_number' => $court['court_number'],
                'sport' => [
                    'name' => $court['sport_name'],
                    'icon' => $court['sport_icon']
                ],
                'price_per_hour' => (int) $court['price_per_hour'],
                'surface_type' => $court['surface_type'],
                'dimensions' => $court['dimensions'] ?? '',
                'size_length' => (float) ($court['size_length'] ?? 0),
                'size_width' => (float) ($court['size_width'] ?? 0),
                'capacity' => (int) $court['capacity'],
                'is_indoor' => (bool) $court['is_indoor'],
                'has_lighting' => (bool) $court['has_lighting'],
                'featured_image' => self::formatImageUrl($court['featured_image'] ?? null),
                'location' => [
                    'city' => $court['city'],
                    'state' => $court['state'],
                    'address' => $court['address']
                ],
                'status' => $court['status']
            ];
        }, $result['items']);
        
        return self::success([
            'courts' => $courts,
            'meta' => $result['meta']
        ]);
    }
    
    /**
     * Get single court details
     */
    private static function show($id)
    {
        $court = self::$db->fetch(
            "SELECT c.*, 
             v.name as venue_name, v.city, v.state, v.address, v.google_map_link, v.whatsapp_number as venue_whatsapp,
             s.name as sport_name, s.icon as sport_icon, s.slug as sport_slug
             FROM courts c
             LEFT JOIN venues v ON c.venue_id = v.id
             LEFT JOIN sports s ON c.sport_id = s.id
             WHERE c.id = ? AND c.deleted_at IS NULL",
            [$id]
        );
        
        if (!$court) {
            return self::error('Court not found', 404, 'COURT_001');
        }
        
        // Get images
        $images = self::$db->fetchAll(
            "SELECT id, image_path, caption, image_type
             FROM court_images
             WHERE court_id = ?
             ORDER BY sort_order ASC",
            [$id]
        );
        
        return self::success([
            'id' => (int) $court['id'],
            'venue' => [
                'id' => (int) $court['venue_id'],
                'name' => $court['venue_name'],
                'city' => $court['city'],
                'state' => $court['state'],
                'address' => $court['address'],
                'google_map_link' => $court['google_map_link'],
                'whatsapp_number' => $court['venue_whatsapp']
            ],
            'name' => $court['name'],
            'court_number' => $court['court_number'],
            'description' => $court['description'],
            'sport' => [
                'name' => $court['sport_name'],
                'slug' => $court['sport_slug'],
                'icon' => $court['sport_icon']
            ],
            'price_per_hour' => (int) $court['price_per_hour'],
            'surface_type' => $court['surface_type'],
            'dimensions' => $court['dimensions'] ?? '',
            'size_length' => (float) ($court['size_length'] ?? 0),
            'size_width' => (float) ($court['size_width'] ?? 0),
            'capacity' => (int) $court['capacity'],
            'min_players' => (int) ($court['min_players'] ?? 1),
            'max_players' => (int) ($court['max_players'] ?? $court['capacity'] ?? 10),
            'is_indoor' => (bool) $court['is_indoor'],
            'has_lighting' => (bool) $court['has_lighting'],
            'has_roof' => (bool) ($court['has_roof'] ?? false),
            'amenities' => json_decode($court['amenities'] ?? '[]', true),
            'equipment' => json_decode($court['equipment_provided'] ?? $court['equipment'] ?? '[]', true),
            'rules' => $court['rules'] ?? '',
            'images' => array_map(function($img) {
                $url = self::formatImageUrl($img['image_path']);
                return [
                    'id' => (int) $img['id'],
                    'url' => $url,
                    'caption' => $img['caption'],
                    'type' => $img['image_type']
                ];
            }, $images),
            'status' => $court['status']
        ]);
    }
    
    /**
     * Get court images
     */
    private static function getImages($id)
    {
        $images = self::$db->fetchAll(
            "SELECT * FROM court_images WHERE court_id = ? ORDER BY sort_order ASC",
            [$id]
        );
        
        return self::success([
            'images' => array_map(function($img) {
                $url = self::formatImageUrl($img['image_path']);
                return [
                    'id' => (int) $img['id'],
                    'url' => $url,
                    'thumbnail' => $url,
                    'caption' => $img['caption'],
                    'type' => $img['image_type'],
                    'sort_order' => (int) $img['sort_order']
                ];
            }, $images)
        ]);
    }
    
    /**
     * Get court availability for a date
     */
    private static function getAvailability($id, $query)
    {
        $date = $query['date'] ?? date('Y-m-d');
        
        // Get court details
        $court = self::$db->fetch(
            "SELECT c.*, v.opening_time, v.closing_time
             FROM courts c
             LEFT JOIN venues v ON c.venue_id = v.id
             WHERE c.id = ?",
            [$id]
        );
        
        if (!$court) {
            return self::error('Court not found', 404);
        }
        
        // Get booked slots
        $bookedSlots = self::$db->fetchAll(
            "SELECT start_time, end_time, status
             FROM bookings
             WHERE court_id = ? AND booking_date = ? AND status IN ('confirmed', 'in_progress', 'pending')",
            [$id, $date]
        );
        
        // Generate available time slots (1 hour intervals)
        $opening = $court['opening_time'] ?? '06:00:00';
        $closing = $court['closing_time'] ?? '22:00:00';
        
        $slots = [];
        $current = strtotime($date . ' ' . $opening);
        $end = strtotime($date . ' ' . $closing);
        
        while ($current < $end) {
            $slotStart = date('H:i:00', $current);
            $slotEnd = date('H:i:00', strtotime('+1 hour', $current));
            
            // Check if slot is booked
            $isBooked = false;
            foreach ($bookedSlots as $booking) {
                if ($slotStart >= $booking['start_time'] && $slotStart < $booking['end_time']) {
                    $isBooked = true;
                    break;
                }
            }
            
            $slots[] = [
                'start_time' => substr($slotStart, 0, 5),
                'end_time' => substr($slotEnd, 0, 5),
                'start_label' => date('g:i A', strtotime($slotStart)),
                'label' => date('g:i A', strtotime($slotStart)) . ' - ' . date('g:i A', strtotime($slotEnd)),
                'is_available' => !$isBooked,
                'is_booked' => $isBooked,
                'price' => (int) $court['price_per_hour']
            ];
            
            $current = strtotime('+1 hour', $current);
        }
        
        return self::success([
            'court_id' => (int) $id,
            'date' => $date,
            'slots' => $slots,
            'venue_timings' => [
                'opening_time' => $opening,
                'closing_time' => $closing
            ]
        ]);
    }
    
    /**
     * Get court reviews
     */
    private static function getReviews($id, $query)
    {
        // Placeholder - implement when reviews are ready
        return self::success([
            'reviews' => [],
            'summary' => [
                'average_rating' => 0,
                'total_reviews' => 0
            ]
        ]);
    }
}
