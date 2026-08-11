<?php
namespace Api\V1;

require_once __DIR__ . '/ApiController.php';

class VenueController extends ApiController
{
    public static function handle($method, $id, $action, $query, $body)
    {
        if ($id && !$action) {
            // GET /venues/{id}
            return $method === 'GET' ? self::show($id) : self::error('Method not allowed', 405);
        }
        
        if ($id && $action) {
            // GET /venues/{id}/{action}
            switch ($action) {
                case 'reviews':
                    return self::getReviews($id, $query);
                case 'images':
                    return self::getImages($id);
                case 'whatsapp':
                    return self::getWhatsAppLink($id);
                case 'availability':
                    return self::getAvailability($id, $query);
                default:
                    return self::error('Invalid action', 404);
            }
        }
        
        // GET /venues or /venues/search
        return $method === 'GET' ? self::index($query) : self::error('Method not allowed', 405);
    }
    
    /**
     * Get all venues with filters
     */
    private static function index($query)
    {
        $page = (int) ($query['page'] ?? 1);
        $perPage = min((int) ($query['per_page'] ?? 20), 50);
        
        $featuredSql = self::venueFeaturedImageSql('v');

        $sql = "SELECT v.*, 
                GROUP_CONCAT(DISTINCT s.name) as sports,
                {$featuredSql} as featured_image,
                (SELECT COUNT(*) FROM courts WHERE venue_id = v.id AND status = 'active') as total_courts,
                (SELECT COUNT(*) FROM courts WHERE venue_id = v.id AND status = 'active' AND id NOT IN (
                    SELECT court_id FROM bookings WHERE booking_date = CURDATE() AND status = 'confirmed'
                )) as available_courts
                FROM venues v
                LEFT JOIN venue_sports vs ON v.id = vs.venue_id
                LEFT JOIN sports s ON vs.sport_id = s.id
                WHERE v.deleted_at IS NULL AND v.status = 'active'";
        
        $params = [];
        
        // Apply filters
        if (!empty($query['city'])) {
            $sql .= " AND LOWER(TRIM(v.city)) = LOWER(TRIM(?))";
            $params[] = $query['city'];
        }
        
        if (!empty($query['sport'])) {
            $sql .= " AND s.slug = ?";
            $params[] = $query['sport'];
        }

        if (!empty($query['sport_name'])) {
            $sql .= " AND s.name LIKE ?";
            $params[] = '%' . $query['sport_name'] . '%';
        }
        
        if (!empty($query['search'])) {
            $sql .= " AND (v.name LIKE ? OR v.description LIKE ? OR v.city LIKE ?)";
            $search = '%' . $query['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        if (!empty($query['min_price'])) {
            $sql .= " AND v.price_per_hour >= ?";
            $params[] = $query['min_price'];
        }
        
        if (!empty($query['max_price'])) {
            $sql .= " AND v.price_per_hour <= ?";
            $params[] = $query['max_price'];
        }
        
        $sql .= " GROUP BY v.id";
        
        // Apply sorting
        $sort = $query['sort'] ?? 'recent';
        switch ($sort) {
            case 'rating':
                $sql .= " ORDER BY v.rating DESC";
                break;
            case 'price':
                $sql .= " ORDER BY v.price_per_hour ASC";
                break;
            case 'distance':
                // Calculate distance if lat/lng provided
                if (!empty($query['latitude']) && !empty($query['longitude'])) {
                    $lat = $query['latitude'];
                    $lng = $query['longitude'];
                    $sql .= " ORDER BY (6371 * acos(cos(radians(?)) * cos(radians(v.latitude)) * cos(radians(v.longitude) - radians(?)) + sin(radians(?)) * sin(radians(v.latitude)))) ASC";
                    array_unshift($params, $lat, $lng, $lat);
                } else {
                    $sql .= " ORDER BY v.created_at DESC";
                }
                break;
            default:
                $sql .= " ORDER BY v.created_at DESC";
        }
        
        $result = self::paginate($sql, $params, $page, $perPage);
        
        // Format venues
        $venues = array_map(function($venue) use ($query) {
            $amenitiesRaw = json_decode($venue['amenities'] ?? '[]', true);
            $amenitiesArray = is_array($amenitiesRaw) ? $amenitiesRaw : [];
            $amenitiesLower = array_map('strtolower', array_map('trim', $amenitiesArray));
            $contains = fn(...$keys) => (bool) array_intersect(array_map(fn($k) => strtolower(trim($k)), $keys), $amenitiesLower);

            return [
                'id' => (int) $venue['id'],
                'name' => $venue['name'],
                'slug' => strtolower(str_replace(' ', '-', $venue['name'])),
                'city' => $venue['city'],
                'state' => $venue['state'],
                'address' => $venue['address'],
                'pincode' => $venue['pincode'],
                'latitude' => (float) $venue['latitude'],
                'longitude' => (float) $venue['longitude'],
                'price_per_hour' => (int) $venue['price_per_hour'],
                'rating' => (float) $venue['rating'],
                'total_reviews' => (int) $venue['total_reviews'],
                'featured_image' => self::formatImageUrl($venue['featured_image'] ?? null),
                'sports' => $venue['sports'] ? explode(',', $venue['sports']) : [],
                'amenities' => $amenitiesArray,
                'has_floodlights' => $contains('Floodlights', 'Flood Lights', 'floodlight'),
                'has_parking' => $contains('Parking', 'Car Parking'),
                'has_water' => $contains('Water', 'Drinking Water', 'Water Bottle'),
                'has_restroom' => $contains('Restroom', 'Washroom', 'Toilet', 'Bathroom'),
                'has_changing_room' => $contains('Changing Room', 'Changing Rooms', 'Locker Room'),
                'has_first_aid' => $contains('First Aid', 'First-Aid', 'Medical'),
                'is_verified' => (bool) $venue['is_verified'],
                'total_courts' => (int) $venue['total_courts'],
                'available_courts' => (int) $venue['available_courts'],
                'opening_time' => $venue['opening_time'],
                'closing_time' => $venue['closing_time'],
                'status' => $venue['status']
            ];
        }, $result['items']);
        
        return self::success([
            'venues' => $venues,
            'meta' => $result['meta']
        ]);
    }
    
    /**
     * Get single venue details
     */
    private static function show($id)
    {
        $featuredSql = self::venueFeaturedImageSql('v');

        $venue = self::$db->fetch(
            "SELECT v.*, u.name as owner_name, u.phone as owner_phone, u.whatsapp_number as owner_whatsapp,
                    {$featuredSql} as featured_image
             FROM venues v
             LEFT JOIN users u ON v.owner_id = u.id
             WHERE v.id = ? AND v.deleted_at IS NULL",
            [$id]
        );
        
        if (!$venue) {
            return self::error('Playground not found', 404, 'VENUE_001');
        }
        
        // Get sports
        $sports = self::$db->fetchAll(
            "SELECT s.* FROM sports s
             JOIN venue_sports vs ON s.id = vs.sport_id
             WHERE vs.venue_id = ? AND s.is_active = 1",
            [$id]
        );
        
        // Get images
        $images = self::$db->fetchAll(
            "SELECT id, image_path, caption, image_type
             FROM venue_images
             WHERE venue_id = ?
             ORDER BY sort_order ASC",
            [$id]
        );
        
        // Get courts with court_image
        $courts = self::$db->fetchAll(
            "SELECT c.*, s.name as sport,
                    (SELECT image_path FROM court_images WHERE court_id = c.id ORDER BY (image_type = 'featured') DESC, sort_order ASC LIMIT 1) as court_image
             FROM courts c
             LEFT JOIN sports s ON c.sport_id = s.id
             WHERE c.venue_id = ? AND c.deleted_at IS NULL",
            [$id]
        );
        
        $amenitiesRaw = json_decode($venue['amenities'] ?? '[]', true);
        $amenitiesArray = is_array($amenitiesRaw) ? $amenitiesRaw : [];
        $amenitiesLower = array_map('strtolower', array_map('trim', $amenitiesArray));
        $contains = fn(...$keys) => (bool) array_intersect(array_map(fn($k) => strtolower(trim($k)), $keys), $amenitiesLower);

        return self::success([
            'id' => (int) $venue['id'],
            'name' => $venue['name'],
            'description' => $venue['description'],
            'city' => $venue['city'],
            'state' => $venue['state'],
            'address' => $venue['address'],
            'pincode' => $venue['pincode'],
            'google_map_link' => $venue['google_map_link'],
            'latitude' => (float) $venue['latitude'],
            'longitude' => (float) $venue['longitude'],
            'price_per_hour' => (int) $venue['price_per_hour'],
            'rating' => (float) $venue['rating'],
            'total_reviews' => (int) $venue['total_reviews'],
            'featured_image' => self::formatImageUrl($venue['featured_image'] ?? null),
            'sports' => $sports,
            'amenities' => $amenitiesArray,
            'has_floodlights' => $contains('Floodlights', 'Flood Lights', 'floodlight'),
            'has_parking' => $contains('Parking', 'Car Parking'),
            'has_water' => $contains('Water', 'Drinking Water', 'Water Bottle'),
            'has_restroom' => $contains('Restroom', 'Washroom', 'Toilet', 'Bathroom'),
            'has_changing_room' => $contains('Changing Room', 'Changing Rooms', 'Locker Room'),
            'has_first_aid' => $contains('First Aid', 'First-Aid', 'Medical'),
            'images' => array_map(function($img) {
                $url = self::formatImageUrl($img['image_path']);
                return [
                    'id' => (int) $img['id'],
                    'url' => $url,
                    'image_path' => $url,
                    'caption' => $img['caption'],
                    'type' => $img['image_type']
                ];
            }, $images),
            'courts' => array_map(function($court) {
                return [
                    'id' => (int) $court['id'],
                    'name' => $court['name'],
                    'court_number' => $court['court_number'],
                    'description' => $court['description'] ?? '',
                    'sport' => $court['sport'],
                    'price_per_hour' => (int) $court['price_per_hour'],
                    'surface_type' => $court['surface_type'],
                    'capacity' => (int) $court['capacity'],
                    'is_indoor' => (bool) $court['is_indoor'],
                    'has_lighting' => (bool) $court['has_lighting'],
                    'image_url' => self::formatImageUrl($court['court_image'] ?? null),
                    'status' => $court['status']
                ];
            }, $courts),
            'owner' => [
                'name' => $venue['owner_name'],
                'phone' => $venue['owner_phone'],
                'whatsapp_number' => $venue['owner_whatsapp']
            ],
            'contact_person' => $venue['contact_person'],
            'contact_email' => $venue['contact_email'],
            'contact_phone' => $venue['contact_phone'],
            'whatsapp_number' => $venue['whatsapp_number'],
            'opening_time' => $venue['opening_time'],
            'closing_time' => $venue['closing_time'],
            'booking_advance_days' => (int) $venue['booking_advance_days'],
            'is_verified' => (bool) $venue['is_verified'],
            'badge_expires_at' => $venue['badge_expires_at'],
            'status' => $venue['status']
        ]);
    }
    
    /**
     * Get venue images
     */
    private static function getImages($id)
    {
        $images = self::$db->fetchAll(
            "SELECT * FROM venue_images WHERE venue_id = ? ORDER BY sort_order ASC",
            [$id]
        );
        
        $imageList = array_map(function($img) {
            $url = self::formatImageUrl($img['image_path']);
            return [
                'id' => (int) $img['id'],
                'url' => $url,
                'image_path' => $url,
                'thumbnail' => $url,
                'caption' => $img['caption'] ?? 'Playground photo',
                'type' => $img['image_type'] ?? 'gallery',
                'sort_order' => (int) ($img['sort_order'] ?? 0)
            ];
        }, $images);
        
        return self::success([
            'images' => $imageList
        ]);
    }
    
    /**
     * Get WhatsApp contact link
     */
    private static function getWhatsAppLink($id)
    {
        $venue = self::$db->fetch(
            "SELECT name, whatsapp_number FROM venues WHERE id = ?",
            [$id]
        );
        
        $whatsappNum = $venue['whatsapp_number'] ?? '+919558346768';
        $phone = preg_replace('/\D/', '', $whatsappNum);
        $venueName = $venue['name'] ?? 'Playground';
        $message = urlencode("Hi, I want to book a slot at " . $venueName);
        
        return self::success([
            'whatsapp_link' => "https://wa.me/{$phone}?text={$message}",
            'phone' => $whatsappNum,
            'contact_name' => $venueName
        ]);
    }
    
    /**
     * Get venue reviews dynamically from DB
     */
    private static function getReviews($id, $query)
    {
        $reviews = self::$db->fetchAll(
            "SELECT r.*, u.name as user_name, u.avatar as user_avatar 
             FROM reviews r 
             LEFT JOIN users u ON r.user_id = u.id 
             WHERE r.venue_id = ? AND r.status = 'approved'
             ORDER BY r.created_at DESC",
            [$id]
        );

        $total = count($reviews);
        $sum = array_reduce($reviews, fn($carry, $item) => $carry + ($item['rating'] ?? 5), 0);
        $venueRating = (float) self::$db->fetchColumn("SELECT rating FROM venues WHERE id = ?", [$id]);
        $avg = $total > 0 ? round($sum / $total, 1) : ($venueRating ?: 5.0);
        
        return self::success([
            'reviews' => array_map(function($r) {
                return [
                    'id' => (int) $r['id'],
                    'user_name' => $r['user_name'] ?? 'Player',
                    'rating' => (int) $r['rating'],
                    'comment' => $r['review'] ?? $r['comment'] ?? '',
                    'created_at' => $r['created_at']
                ];
            }, $reviews),
            'summary' => [
                'average_rating' => $avg,
                'total_reviews' => $total
            ]
        ]);
    }
    
    /**
     * Get availability with real slot generation & occupancy calculation
     */
    private static function getAvailability($id, $query)
    {
        $date = $query['date'] ?? date('Y-m-d');
        
        $venue = self::$db->fetch(
            "SELECT opening_time, closing_time, price_per_hour FROM venues WHERE id = ?",
            [$id]
        );

        $openTime = $venue['opening_time'] ?? '06:00:00';
        $closeTime = $venue['closing_time'] ?? '23:00:00';
        $price = (int) ($venue['price_per_hour'] ?? 1000);

        $startHour = (int) explode(':', $openTime)[0];
        $endHour = (int) explode(':', $closeTime)[0];
        if ($endHour <= $startHour) $endHour = 23;

        // Fetch existing bookings for this date and venue
        $existingBookings = self::$db->fetchAll(
            "SELECT start_time, end_time, status FROM bookings 
             WHERE venue_id = ? AND booking_date = ? AND status IN ('confirmed', 'pending', 'completed')",
            [$id, $date]
        );

        $slots = [];
        $totalSlots = 0;
        $bookedSlots = 0;

        for ($h = $startHour; $h < $endHour; $h++) {
            $totalSlots++;
            $sTimeStr = sprintf('%02d:00', $h);
            $eTimeStr = sprintf('%02d:00', $h + 1);

            $sLabel = date('g:i A', strtotime($sTimeStr));
            $eLabel = date('g:i A', strtotime($eTimeStr));

            $isBooked = false;
            foreach ($existingBookings as $b) {
                $bStart = substr($b['start_time'], 0, 5);
                $bEnd = substr($b['end_time'], 0, 5);
                if ($sTimeStr < $bEnd && $eTimeStr > $bStart) {
                    $isBooked = true;
                    break;
                }
            }
            if ($isBooked) {
                $bookedSlots++;
            }

            $slots[] = [
                'start_time' => $sTimeStr,
                'end_time' => $eTimeStr,
                'label' => "{$sLabel} - {$eLabel}",
                'start_label' => $sLabel,
                'price' => $price,
                'is_available' => !$isBooked,
                'is_booked' => $isBooked
            ];
        }

        $availableSlots = $totalSlots - $bookedSlots;
        $occupancyPercent = $totalSlots > 0 ? round(($bookedSlots / $totalSlots) * 100) : 0;

        return self::success([
            'venue_id' => (int) $id,
            'date' => $date,
            'slots' => $slots,
            'summary' => [
                'total_slots' => $totalSlots,
                'booked_slots' => $bookedSlots,
                'available_slots' => $availableSlots,
                'occupancy_percentage' => $occupancyPercent
            ]
        ]);
    }
}
