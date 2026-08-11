<?php
namespace Api\V1;

require_once __DIR__ . '/ApiController.php';

class BookingController extends ApiController
{
    public static function handle($method, $id, $action, $query, $body)
    {
        self::requireAuth();
        
        if ($id && $action === 'cancel') {
            return $method === 'POST' ? self::cancel($id) : self::error('Method not allowed', 405);
        }
        
        if ($id && !$action) {
            return $method === 'GET' ? self::show($id) : self::error('Method not allowed', 405);
        }
        
        if ($method === 'GET') {
            return self::index($query);
        }
        
        if ($method === 'POST') {
            return self::create($body);
        }
        
        return self::error('Method not allowed', 405);
    }
    
    /**
     * Get user's bookings
     */
    private static function index($query)
    {
        $page = (int) ($query['page'] ?? 1);
        $perPage = min((int) ($query['per_page'] ?? 20), 50);
        
        $sql = "SELECT b.*,
                v.name as venue_name, v.city, v.address, v.whatsapp_number as venue_whatsapp,
                c.name as court_name, c.court_number,
                s.name as sport_name, s.icon as sport_icon
                FROM bookings b
                LEFT JOIN venues v ON b.venue_id = v.id
                LEFT JOIN courts c ON b.court_id = c.id
                LEFT JOIN sports s ON c.sport_id = s.id
                WHERE b.user_id = ?";
        
        $params = [self::$user['id']];
        
        // Filter by status
        if (!empty($query['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $query['status'];
        }
        
        // Filter by date range
        if (!empty($query['from_date'])) {
            $sql .= " AND b.booking_date >= ?";
            $params[] = $query['from_date'];
        }
        
        if (!empty($query['to_date'])) {
            $sql .= " AND b.booking_date <= ?";
            $params[] = $query['to_date'];
        }
        
        $sql .= " ORDER BY b.booking_date DESC, b.start_time DESC";
        
        $result = self::paginate($sql, $params, $page, $perPage);
        
        $bookings = array_map(function($booking) {
            return [
                'id' => (int) $booking['id'],
                'booking_number' => $booking['booking_reference'] ?? $booking['booking_number'] ?? '',
                'venue' => [
                    'id' => (int) $booking['venue_id'],
                    'name' => $booking['venue_name'],
                    'city' => $booking['city'],
                    'address' => $booking['address'],
                    'whatsapp_number' => $booking['venue_whatsapp']
                ],
                'court' => [
                    'id' => (int) $booking['court_id'],
                    'name' => $booking['court_name'],
                    'court_number' => $booking['court_number']
                ],
                'sport' => [
                    'name' => $booking['sport_name'],
                    'icon' => $booking['sport_icon']
                ],
                'booking_date' => $booking['booking_date'],
                'start_time' => substr($booking['start_time'], 0, 5),
                'end_time' => substr($booking['end_time'], 0, 5),
                'duration_hours' => (float) ($booking['total_hours'] ?? $booking['duration_hours'] ?? 0),
                'total_amount' => (int) ($booking['amount'] ?? $booking['total_amount'] ?? 0),
                'payment_status' => $booking['payment_status'],
                'status' => $booking['status'],
                'created_at' => $booking['created_at']
            ];
        }, $result['items']);
        
        return self::success([
            'bookings' => $bookings,
            'meta' => $result['meta']
        ]);
    }
    
    /**
     * Get single booking details
     */
    private static function show($id)
    {
        $booking = self::$db->fetch(
            "SELECT b.*,
             v.name as venue_name, v.city, v.state, v.address, v.google_map_link, v.whatsapp_number as venue_whatsapp,
             c.name as court_name, c.court_number, c.surface_type,
             s.name as sport_name, s.icon as sport_icon,
             u.name as user_name, u.email as user_email, u.phone as user_phone
             FROM bookings b
             LEFT JOIN venues v ON b.venue_id = v.id
             LEFT JOIN courts c ON b.court_id = c.id
             LEFT JOIN sports s ON c.sport_id = s.id
             LEFT JOIN users u ON b.user_id = u.id
             WHERE b.id = ? AND b.user_id = ?",
            [$id, self::$user['id']]
        );
        
        if (!$booking) {
            return self::error('Booking not found', 404, 'BOOKING_001');
        }
        
        return self::success([
            'id' => (int) $booking['id'],
            'booking_number' => $booking['booking_reference'] ?? $booking['booking_number'] ?? '',
            'venue' => [
                'id' => (int) $booking['venue_id'],
                'name' => $booking['venue_name'],
                'city' => $booking['city'],
                'state' => $booking['state'],
                'address' => $booking['address'],
                'google_map_link' => $booking['google_map_link'],
                'whatsapp_number' => $booking['venue_whatsapp']
            ],
            'court' => [
                'id' => (int) $booking['court_id'],
                'name' => $booking['court_name'],
                'court_number' => $booking['court_number'],
                'surface_type' => $booking['surface_type']
            ],
            'sport' => [
                'name' => $booking['sport_name'],
                'icon' => $booking['sport_icon']
            ],
            'user' => [
                'name' => $booking['user_name'],
                'email' => $booking['user_email'],
                'phone' => $booking['user_phone']
            ],
            'booking_date' => $booking['booking_date'],
            'start_time' => substr($booking['start_time'], 0, 5),
            'end_time' => substr($booking['end_time'], 0, 5),
            'duration_hours' => (float) ($booking['total_hours'] ?? $booking['duration_hours'] ?? 0),
            'total_amount' => (int) ($booking['amount'] ?? $booking['total_amount'] ?? 0),
            'payment_method' => $booking['payment_method'],
            'payment_status' => $booking['payment_status'],
            'transaction_id' => $booking['transaction_id'] ?? null,
            'notes' => $booking['notes'],
            'special_requirements' => $booking['special_requirements'] ?? null,
            'status' => $booking['status'],
            'cancellation_reason' => $booking['cancellation_reason'] ?? null,
            'cancelled_at' => $booking['cancelled_at'] ?? null,
            'created_at' => $booking['created_at']
        ]);
    }
    
    /**
     * Create new booking
     */
    private static function create($data)
    {
        require_once __DIR__ . '/../../admin/app/Services/BookingSlotHelper.php';

        // Optional multi-slot payload → merge into one continuous range
        if (!empty($data['slots']) && is_array($data['slots'])) {
            $slotRows = [];
            foreach ($data['slots'] as $slot) {
                if (!is_array($slot)) {
                    continue;
                }
                $slotRows[] = [
                    'start_time' => $slot['start_time'] ?? '',
                    'end_time'   => $slot['end_time'] ?? '',
                ];
            }

            $merged = \App\Services\BookingSlotHelper::mergeConsecutiveSlots($slotRows);
            if ($merged === null) {
                return self::error(
                    'Selected slots must be continuous (e.g. 9:00 AM – 1:00 PM)',
                    422,
                    'BOOKING_003'
                );
            }

            $data['start_time'] = $merged['start_time'];
            $data['end_time'] = $merged['end_time'];
        }

        $startTime = \App\Services\BookingSlotHelper::normalizeTime($data['start_time'] ?? '');
        $endTime = \App\Services\BookingSlotHelper::normalizeTime($data['end_time'] ?? '');

        // Validate required fields
        $required = ['venue_id', 'court_id', 'booking_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return self::error("Field '{$field}' is required", 400, 'VALIDATION_ERROR');
            }
        }

        if ($startTime === '' || $endTime === '' || $endTime <= $startTime) {
            return self::error('Valid start_time and end_time are required', 400, 'VALIDATION_ERROR');
        }

        $data['start_time'] = $startTime;
        $data['end_time'] = $endTime;

        // Check if court exists and is available
        $court = self::$db->fetch(
            "SELECT c.*, v.price_per_hour as venue_price
             FROM courts c
             LEFT JOIN venues v ON c.venue_id = v.id
             WHERE c.id = ? AND c.venue_id = ? AND c.status = 'active'",
            [$data['court_id'], $data['venue_id']]
        );

        if (!$court) {
            return self::error('Court not found or inactive', 404, 'COURT_001');
        }

        // Overlap check (includes pending holds)
        $conflictingBooking = self::$db->fetch(
            "SELECT id FROM bookings
             WHERE court_id = ? AND booking_date = ?
             AND status IN ('confirmed', 'pending', 'completed')
             AND start_time < ? AND end_time > ?",
            [
                $data['court_id'],
                $data['booking_date'],
                $endTime . ':00',
                $startTime . ':00',
            ]
        );

        if ($conflictingBooking) {
            return self::error('Court not available for selected time slot', 409, 'BOOKING_002');
        }

        $durationHours = \App\Services\BookingSlotHelper::hoursBetween($startTime, $endTime);
        $pricePerHour = (float) ($court['price_per_hour'] ?: $court['venue_price'] ?? 0);
        $totalAmount = $durationHours * $pricePerHour;

        // Generate booking number
        $bookingNumber = 'BK' . date('Ymd') . rand(1000, 9999);

        // Create single merged booking row
        $bookingId = self::$db->insert(
            "INSERT INTO bookings (
                booking_reference, user_id, venue_id, court_id, sport_id, booking_date, start_time, end_time,
                total_hours, price_per_hour, subtotal, amount, payment_status, status, notes, created_at, updated_at
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $bookingNumber,
                self::$user['id'],
                $data['venue_id'],
                $data['court_id'],
                $court['sport_id'] ?? null,
                $data['booking_date'],
                $startTime . ':00',
                $endTime . ':00',
                $durationHours,
                $pricePerHour,
                $totalAmount,
                $totalAmount,
                'pending',
                'pending',
                $data['notes'] ?? null,
            ]
        );

        return self::success([
            'booking_id' => $bookingId,
            'booking_number' => $bookingNumber,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'total_hours' => $durationHours,
            'total_amount' => (int) round($totalAmount),
            'message' => 'Booking created successfully. Please complete payment.',
        ], 'Booking created successfully', 201);
    }
    
    /**
     * Cancel booking
     */
    private static function cancel($id)
    {
        $booking = self::$db->fetch(
            "SELECT * FROM bookings WHERE id = ? AND user_id = ?",
            [$id, self::$user['id']]
        );
        
        if (!$booking) {
            return self::error('Booking not found', 404, 'BOOKING_001');
        }
        
        if ($booking['status'] === 'cancelled') {
            return self::error('Booking already cancelled', 400, 'BOOKING_003');
        }
        
        if ($booking['status'] === 'completed') {
            return self::error('Cannot cancel completed booking', 400, 'BOOKING_004');
        }
        
        // Update booking status
        self::$db->update('bookings', $id, [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'cancellation_reason' => 'Cancelled by user',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return self::success([
            'booking_id' => (int) $id,
            'status' => 'cancelled'
        ], 'Booking cancelled successfully');
    }
}
