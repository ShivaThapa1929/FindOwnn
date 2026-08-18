<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;
use App\Core\Request;
use App\Core\Session;
use App\Models\Booking;
use App\Models\Venue;
use App\Models\AuditLog;
use App\Models\ActivityLog;
use App\Services\ValidationService;
use App\Services\BookingSlotHelper;
use App\Services\BookingReminderService;

class BookingController extends Controller
{
    // ── List ─────────────────────────────────────────────────────
    public function index(Request $request): void
    {
        $bookModel = new Booking();
        $page      = max(1, (int) $request->query('page', 1));
        $role      = $this->user()['role'];
        $filter    = $request->query('status', 'all');
        $search    = $request->query('search', '');

        if ($role === 'venue_owner') {
            $ownerId = $this->user()['id'];
            $where   = 'v.owner_id = ?';
            $params  = [$ownerId];
            if ($filter !== 'all') { $where .= ' AND b.status = ?'; $params[] = $filter; }
            if ($search !== '')    { $where .= ' AND (u.name LIKE ? OR b.booking_reference LIKE ? OR v.name LIKE ?)'; $params[] = "%{$search}%"; $params[] = "%{$search}%"; $params[] = "%{$search}%"; }

            $total   = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM bookings b
                 JOIN venues v ON b.venue_id = v.id
                 LEFT JOIN users u ON b.user_id = u.id
                 WHERE {$where}", $params
            );
            $perPage = 20;
            $offset  = ($page - 1) * $perPage;
            $pages   = (int) ceil($total / $perPage);
            $data    = $this->db->fetchAll(
                "SELECT b.*, v.name AS venue_name,
                        u.name AS user_name, u.email AS user_email, u.phone AS user_phone
                 FROM bookings b
                 JOIN venues v ON b.venue_id = v.id
                 LEFT JOIN users u ON b.user_id = u.id
                 WHERE {$where}
                 ORDER BY b.booking_date DESC, b.start_time DESC
                 LIMIT {$perPage} OFFSET {$offset}",
                $params
            );
            $result = compact('data','total','page','perPage','pages');
        } else {
            $result = $bookModel->getAllWithDetails($page, 20, $filter, $search);
        }

        // My venues (for owner's offline booking)
        $myVenues = [];
        if ($role === 'venue_owner') {
            $myVenues = $this->ownerBookableVenues((int) $this->user()['id']);
        }

        $stats = $role === 'venue_owner'
            ? $bookModel->getStatsForOwner((int) $this->user()['id'])
            : $bookModel->getStats();

        $this->render('bookings.index', [
            'title'    => 'Bookings',
            'result'   => $result,
            'stats'    => $stats,
            'filter'   => $filter,
            'search'   => $search,
            'myVenues' => $myVenues,
            'success'  => Session::getFlash('success'),
            'error'    => Session::getFlash('error'),
        ]);
    }

    // ── Show ──────────────────────────────────────────────────────
    public function show(Request $request): void
    {
        $id      = (int) $request->param('id');
        $booking = $this->db->fetch(
            "SELECT b.*, 
                    v.name AS venue_name, v.city AS venue_city, v.address AS venue_address,
                    v.owner_id,
                    COALESCE(c.price_per_hour, v.price_per_hour) AS court_price_per_hour,
                    c.name AS court_name, c.court_number,
                    s.name AS sport_name, s.slug AS sport_slug,
                    u.name AS user_name, u.email AS user_email, u.phone AS user_phone, u.whatsapp_number
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             LEFT JOIN courts c ON b.court_id = c.id
             LEFT JOIN sports s ON b.sport_id = s.id
             LEFT JOIN users u ON b.user_id = u.id
             WHERE b.id = ?",
            [$id]
        );

        if (!$booking) {
            Session::flash('error', 'Booking not found.');
            $this->redirect(url('/bookings'));
        }

        if ($this->hasRole('venue_owner') && $booking['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/bookings'));
        }

        // Use stored price_per_hour if exists, else use court price
        if (empty($booking['price_per_hour']) || $booking['price_per_hour'] == 0) {
            $booking['price_per_hour'] = $booking['court_price_per_hour'];
        }

        // Calculate pricing breakdown
        $subtotal = $booking['price_per_hour'] * $booking['total_hours'];
        $booking['subtotal'] = $booking['subtotal'] ?? $subtotal;
        $booking['discount_amount'] = $booking['discount_amount'] ?? 0;
        $booking['discount_percent'] = $booking['discount_percent'] ?? 0;

        $this->render('bookings.show', [
            'title'   => 'Booking ' . e($booking['booking_reference']),
            'booking' => $booking,
        ]);
    }

    // ── Slot Booking View ──────────────────────────────────────────
    public function slots(Request $request): void
    {
        $venue_id = $request->query('venue_id');
        $court_id = $request->query('court_id');
        $date = $request->query('date', date('Y-m-d'));

        // Get all venues (filtered by owner if venue_owner role)
        if ($this->hasRole('venue_owner')) {
            $venues = $this->db->fetchAll(
                "SELECT id, name FROM venues 
                 WHERE owner_id = ? AND deleted_at IS NULL 
                 ORDER BY name",
                [$this->user()['id']]
            );
        } else {
            $venues = $this->db->fetchAll(
                "SELECT id, name FROM venues 
                 WHERE deleted_at IS NULL 
                 ORDER BY name"
            );
        }

        $venue = null;
        $court = null;
        $slots = [];
        $courts = [];

        // Get venue and court info if selected
        if ($venue_id && $court_id) {
            $venue = $this->db->fetch("SELECT * FROM venues WHERE id = ?", [$venue_id]);
            $court = $this->db->fetch(
                "SELECT c.*, s.name as sport_name 
                 FROM courts c 
                 LEFT JOIN sports s ON c.sport_id = s.id 
                 WHERE c.id = ?",
                [$court_id]
            );

            // Generate time slots - 24 hour coverage
            if ($venue && $court) {
                // Force 24-hour operation: 12 AM to 11 PM
                $opening = '00:00:00';
                $closing = '23:00:00'; // Last slot starts at 11 PM

                $currentDate = strtotime($date . ' ' . $opening);
                $endDate = strtotime($date . ' ' . $closing);
                $now = time();

                while ($currentDate <= $endDate) {
                    $slotStart = date('H:i:00', $currentDate);
                    $slotEnd = date('H:i:00', strtotime('+1 hour', $currentDate));
                    $slotDateTime = strtotime($date . ' ' . $slotStart);

                    // Skip past time slots completely (don't show them at all)
                    if ($date === date('Y-m-d') && $slotDateTime < $now) {
                        $currentDate = strtotime('+1 hour', $currentDate);
                        continue; // Skip this slot entirely
                    }

                    // Check if slot is booked - Overlap detection
                    // A booking overlaps with this slot if:
                    // booking_start_time < slot_end_time AND booking_end_time > slot_start_time
                    $booking = $this->db->fetch(
                        "SELECT b.id, b.booking_reference, b.start_time, b.end_time, b.status,
                                b.amount as total_amount, u.name as user_name
                         FROM bookings b 
                         LEFT JOIN users u ON b.user_id = u.id
                         WHERE b.court_id = ? 
                         AND b.booking_date = ? 
                         AND TIME(b.start_time) < TIME(?) 
                         AND TIME(b.end_time) > TIME(?)
                         AND b.status IN ('confirmed', 'completed', 'in_progress', 'pending')
                         LIMIT 1",
                        [$court_id, $date, $slotEnd, $slotStart]
                    );

                    $slots[] = [
                        'start_time' => $slotStart,
                        'end_time' => $slotEnd,
                        'is_booked' => $booking ? true : false,
                        'is_past' => false, // Past slots are now hidden, so this is always false
                        'booking' => $booking
                    ];

                    $currentDate = strtotime('+1 hour', $currentDate);
                }

                $slots = BookingSlotHelper::mergeBookedSlotDisplay($slots);
            }
        }

        // Get courts for selected venue
        if ($venue_id) {
            $courts = $this->db->fetchAll(
                "SELECT c.id, c.name, c.court_number 
                 FROM courts c 
                 WHERE c.venue_id = ? AND c.deleted_at IS NULL",
                [$venue_id]
            );
        }

        $this->render('bookings.slots', [
            'title' => 'Booking Slots',
            'venues' => $venues,
            'courts' => $courts,
            'venue_id' => $venue_id,
            'court_id' => $court_id,
            'date' => $date,
            'venue' => $venue,
            'court' => $court,
            'slots' => $slots
        ]);
    }

    // ── Owner / Admin: Create Offline Booking ─────────────────────────────
    public function createOffline(Request $request): void
    {
        $role = $this->user()['role'];
        if (in_array($role, ['super_admin', 'admin'])) {
            $myVenues = $this->db->fetchAll(
                "SELECT id, name, price_per_hour, status, verification_status FROM venues
                 WHERE deleted_at IS NULL AND status != 'suspended' ORDER BY name"
            );
        } else {
            $myVenues = $this->ownerBookableVenues((int) $this->user()['id']);
        }

        $venueId   = $request->query('venue_id');
        $courtId   = $request->query('court_id');
        $date      = $request->query('date', date('Y-m-d'));
        $startTime = $request->query('start_time');
        $endTime   = $request->query('end_time');

        $old    = $_SESSION['old_input'] ?? [];
        $errors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        // Merge query parameters if not already set from form post submit
        if (!isset($old['venue_id']) && $venueId)     $old['venue_id'] = $venueId;
        if (!isset($old['court_id']) && $courtId)     $old['court_id'] = $courtId;
        if (!isset($old['booking_date']) && $date)    $old['booking_date'] = $date;
        if (!isset($old['start_time']) && $startTime) $old['start_time'] = substr($startTime, 0, 5);
        if (!isset($old['end_time']) && $endTime)     $old['end_time'] = substr($endTime, 0, 5);

        $venueCourts = [];
        $selectedVenueId = (int) ($old['venue_id'] ?? 0);
        if ($selectedVenueId > 0) {
            $venueCourts = $this->db->fetchAll(
                "SELECT id, name, court_number, price_per_hour, sport_id, status
                 FROM courts
                 WHERE venue_id = ? AND deleted_at IS NULL
                 ORDER BY court_number ASC, name ASC",
                [$selectedVenueId]
            );
        }

        $this->render('bookings.create-offline', [
            'title'       => 'Add Offline Booking',
            'myVenues'    => $myVenues,
            'venueCourts' => $venueCourts,
            'noVenues'    => empty($myVenues),
            'old'         => $old,
            'errors'      => $errors,
        ]);
    }

    // ── Owner / Admin: Store Offline Booking ──────────────────────────────
    public function storeOffline(Request $request): void
    {
        $role     = $this->user()['role'];
        $ownerId  = $this->user()['id'];
        $venueId  = (int) $request->input('venue_id', 0);
        $courtId  = (int) $request->input('court_id', 0);

        // Confirm venue belongs to owner (active not required — owners record walk-ins at their venues)
        if (in_array($role, ['super_admin', 'admin'])) {
            $venue = $this->db->fetch(
                "SELECT * FROM venues WHERE id = ? AND deleted_at IS NULL AND status != 'suspended'",
                [$venueId]
            );
        } else {
            $venue = $this->db->fetch(
                "SELECT * FROM venues WHERE id = ? AND owner_id = ? AND deleted_at IS NULL AND status != 'suspended'",
                [$venueId, $ownerId]
            );
        }

        // Confirm court belongs to the venue
        $court = null;
        if ($venue && $courtId) {
            $court = $this->db->fetch(
                "SELECT * FROM courts WHERE id = ? AND venue_id = ? AND deleted_at IS NULL",
                [$courtId, $venueId]
            );
        }

        $startTime = BookingSlotHelper::normalizeTime($request->input('start_time', ''));
        $endTime   = BookingSlotHelper::normalizeTime($request->input('end_time', ''));

        $v = new ValidationService();
        $v->custom($venue !== false && $venue !== null, 'venue_id', 'Invalid venue or you do not have access to it.')
          ->custom(!empty($court), 'court_id', 'Please select a valid court for the selected venue.')
          ->required($request->input('booking_date'), 'booking_date', 'Booking date')
          ->date($request->input('booking_date', ''), 'booking_date', 'Booking date')
          ->custom(
              $request->input('booking_date', '') === '' ||
              strtotime($request->input('booking_date')) >= strtotime(date('Y-m-d')),
              'booking_date', 'Booking date cannot be in the past.'
          )
          ->required($startTime, 'start_time', 'Start time')
          ->time($startTime, 'start_time', 'Start time')
          ->required($endTime, 'end_time', 'End time')
          ->time($endTime, 'end_time', 'End time')
          ->required($request->input('customer_name'), 'customer_name', 'Customer name')
          ->minLength($request->input('customer_name', ''), 'customer_name', 2, 'Customer name');

        if ($startTime) {
            $startParts = explode(':', $startTime);
            $v->custom(
                isset($startParts[1]) && (int) $startParts[1] === 0,
                'start_time',
                'Start time must be on the hour (e.g., 5:00, 6:00). Half-hour bookings are not allowed.'
            );
        }
        if ($endTime) {
            $endParts = explode(':', $endTime);
            $v->custom(
                isset($endParts[1]) && (int) $endParts[1] === 0,
                'end_time',
                'End time must be on the hour (e.g., 6:00, 7:00). Half-hour bookings are not allowed.'
            );
        }

        $startTs = strtotime($request->input('booking_date') . ' ' . $startTime);
        $endTs   = strtotime($request->input('booking_date') . ' ' . $endTime);
        $v->custom($endTs > $startTs, 'end_time', 'End time must be after start time.');

        if ($v->fails()) {
            $_SESSION['old_input']         = $_POST;
            $_SESSION['validation_errors'] = $v->errors();
            Session::flash('error', $v->firstError());
            $this->redirect(url('/bookings/offline/create'));
        }

        try {
            $conflict = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM bookings
                 WHERE court_id = ?
                   AND booking_date = ?
                   AND status NOT IN ('cancelled')
                   AND start_time < ? AND end_time > ?",
                [
                    $courtId,
                    $request->input('booking_date'),
                    $endTime . ':00',
                    $startTime . ':00',
                ]
            );

            if ($conflict > 0) {
                Session::flash('error', 'This court is already booked for this time slot. Please choose a different time or court.');
                $_SESSION['old_input'] = $_POST;
                $this->redirect(url('/bookings/offline/create'));
            }

            $hours        = round(($endTs - $startTs) / 3600, 2);
            $pricePerHour = (float) ($court['price_per_hour'] ?? 0);
            $subtotal     = round($hours * $pricePerHour, 2);
            $amount       = $subtotal;

            $customAmount = $request->input('custom_amount', '');
            if ($customAmount !== '' && is_numeric($customAmount)) {
                $amount = round((float) $customAmount, 2);
            }

            $custName  = trim($request->input('customer_name'));
            $custPhone = trim($request->input('customer_phone', ''));
            $custEmail = trim($request->input('customer_email', ''));

            $userId = $this->findOrCreateWalkInCustomer($custName, $custPhone, $custEmail);

            $ref = 'OFL-' . strtoupper(substr(uniqid(), -7));

            $bookingId = $this->insertOfflineBooking(
                $venueId,
                $courtId,
                $court,
                $userId,
                $request->input('booking_date'),
                $startTime,
                $endTime,
                $hours,
                $pricePerHour,
                $subtotal,
                $amount,
                $request->input('payment_status', 'pending'),
                $ref,
                '[OFFLINE] ' . trim($request->input('notes', ''))
            );

            try {
                ActivityLog::record(
                    "Created booking {$ref} for {$custName}",
                    'booking', 'Booking', (int) $bookingId
                );
                AuditLog::log('OFFLINE_BOOKING_CREATED', 'Booking', (int) $bookingId, [],
                    ['venue_id' => $venueId, 'court_id' => $courtId, 'date' => $request->input('booking_date'), 'ref' => $ref]);
            } catch (\Throwable $logError) {
                error_log('[Findownn Offline Booking] Log failed: ' . $logError->getMessage());
            }

            Session::flash('success', "Offline booking {$ref} created successfully.");
            $this->redirect(url('/bookings/' . $bookingId));
        } catch (\Throwable $e) {
            error_log('[Findownn Offline Booking] ' . $e->getMessage());
            $_SESSION['old_input'] = $_POST;
            Session::flash(
                'error',
                Config::get('APP_DEBUG') === 'true'
                    ? 'Could not create booking: ' . $e->getMessage()
                    : 'Could not create booking. Please check court selection and try again.'
            );
            $this->redirect(url('/bookings/offline/create'));
        }
    }

    /** Venues an owner can use for offline / walk-in bookings. */
    private function ownerBookableVenues(int $ownerId): array
    {
        return $this->db->fetchAll(
            "SELECT id, name, price_per_hour, status, verification_status
             FROM venues
             WHERE owner_id = ? AND deleted_at IS NULL AND status != 'suspended'
             ORDER BY name",
            [$ownerId]
        );
    }

    /**
     * Insert offline booking — tries full row first, falls back for older DB schemas.
     */
    private function insertOfflineBooking(
        int $venueId,
        int $courtId,
        array $court,
        int $userId,
        string $bookingDate,
        string $startTime,
        string $endTime,
        float $hours,
        float $pricePerHour,
        float $subtotal,
        float $amount,
        string $paymentStatus,
        string $ref,
        string $notes
    ): int {
        $sportId = (int) ($court['sport_id'] ?? 0) ?: null;

        try {
            return (int) $this->db->insert(
                "INSERT INTO bookings
                 (venue_id, court_id, sport_id, user_id, booking_date, start_time, end_time,
                  total_hours, price_per_hour, subtotal, amount,
                  status, payment_status, booking_reference, notes, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,'confirmed',?,?,?,NOW(),NOW())",
                [
                    $venueId, $courtId, $sportId, $userId, $bookingDate,
                    $startTime . ':00', $endTime . ':00',
                    $hours, $pricePerHour, $subtotal, $amount,
                    $paymentStatus, $ref, $notes,
                ]
            );
        } catch (\Throwable $e) {
            error_log('[Findownn Offline Booking] Extended insert failed, using fallback: ' . $e->getMessage());

            return (int) $this->db->insert(
                "INSERT INTO bookings
                 (venue_id, court_id, user_id, booking_date, start_time, end_time, total_hours, amount,
                  status, payment_status, booking_reference, notes, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,?,?,'confirmed',?,?,?,NOW(),NOW())",
                [
                    $venueId, $courtId, $userId, $bookingDate,
                    $startTime . ':00', $endTime . ':00',
                    $hours, $amount, $paymentStatus, $ref, $notes,
                ]
            );
        }
    }

    /** Find existing player/walk-in or create a guest user for offline bookings. */
    private function findOrCreateWalkInCustomer(string $name, string $phone, string $email): int
    {
        if ($email !== '') {
            $existing = $this->db->fetch("SELECT id FROM users WHERE email = ? AND deleted_at IS NULL", [$email]);
            if ($existing) {
                return (int) $existing['id'];
            }
        }

        if ($phone !== '') {
            $digits = preg_replace('/\D/', '', $phone);
            $existing = $this->db->fetch(
                "SELECT id FROM users WHERE REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', '') = ? AND deleted_at IS NULL",
                [$digits]
            );
            if ($existing) {
                return (int) $existing['id'];
            }
        }

        $walkInEmail = $phone !== ''
            ? 'walkin_' . preg_replace('/\D/', '', $phone) . '@offline.findownn'
            : 'walkin_' . time() . '_' . bin2hex(random_bytes(3)) . '@offline.findownn';

        $password = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);

        try {
            return (int) $this->db->insert(
                "INSERT INTO users (name, email, password, phone, whatsapp_number, whatsapp_opt_in, role, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, 1, 'player', 'active', NOW(), NOW())",
                [$name, $walkInEmail, $password, $phone ?: null, $phone ?: null]
            );
        } catch (\Throwable $e) {
            return (int) $this->db->insert(
                "INSERT INTO users (name, email, password, phone, role, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, 'player', 'active', NOW(), NOW())",
                [$name, $walkInEmail, $password, $phone ?: null]
            );
        }
    }

    // ── Admin / Owner: Update Status ──────────────────────────────
    public function updateStatus(Request $request): void
    {
        $id      = (int) $request->param('id');
        $status  = $request->input('status');
        $allowed = ['pending','confirmed','cancelled','completed'];

        if (!in_array($status, $allowed)) {
            Session::flash('error', 'Invalid status value.');
            $this->redirect(url('/bookings'));
        }

        $old = $this->db->fetch(
            "SELECT b.*, v.owner_id FROM bookings b JOIN venues v ON b.venue_id = v.id WHERE b.id = ?",
            [$id]
        );
        if (!$old) {
            Session::flash('error', 'Booking not found.');
            $this->redirect(url('/bookings'));
        }

        // Owners can only update their own venue bookings
        if ($this->hasRole('venue_owner') && $old['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/bookings'));
        }

        $this->db->execute(
            "UPDATE bookings SET status = ?, updated_at = ? WHERE id = ?",
            [$status, now(), $id]
        );

        AuditLog::log('BOOKING_STATUS_CHANGED', 'Booking', $id,
            ['status' => $old['status']], ['status' => $status]);
        ActivityLog::record(
            "{$old['booking_reference']} → {$status}",
            'booking', 'Booking', $id
        );

        if ($request->isAjax()) {
            $this->json(['success' => true, 'status' => $status]);
        }

        Session::flash('success', "Booking status updated to {$status}.");
        $this->redirect(url('/bookings/' . $id));
    }

    // ── Admin / Owner: Update Payment Status ─────────────────────
    public function updatePayment(Request $request): void
    {
        $id            = (int) $request->param('id');
        $paymentStatus = $request->input('payment_status');
        $allowed       = ['pending','paid','refunded','failed'];

        if (!in_array($paymentStatus, $allowed)) {
            Session::flash('error', 'Invalid payment status.');
            $this->redirect(url('/bookings/' . $id));
        }

        $old = $this->db->fetch(
            "SELECT b.*, v.owner_id FROM bookings b JOIN venues v ON b.venue_id = v.id WHERE b.id = ?",
            [$id]
        );
        if (!$old) {
            Session::flash('error', 'Booking not found.');
            $this->redirect(url('/bookings'));
        }

        if ($this->hasRole('venue_owner') && $old['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/bookings'));
        }

        $this->db->execute(
            "UPDATE bookings SET payment_status = ?, updated_at = ? WHERE id = ?",
            [$paymentStatus, now(), $id]
        );

        // When marked paid manually, ensure booking is at least confirmed (not pending)
        if ($paymentStatus === 'paid' && in_array($old['status'], ['pending'], true)) {
            $this->db->execute(
                "UPDATE bookings SET status = 'confirmed', updated_at = ? WHERE id = ? AND status = 'pending'",
                [now(), $id]
            );
        }

        AuditLog::log('BOOKING_PAYMENT_UPDATED', 'Booking', $id,
            ['payment_status' => $old['payment_status']], ['payment_status' => $paymentStatus]);

        Session::flash('success', "Payment status updated to {$paymentStatus}.");
        $this->redirect(url('/bookings/' . $id));
    }

    public function sendReminder(Request $request): void
    {
        $id = (int) $request->param('id');

        $booking = $this->db->fetch(
            "SELECT b.*, v.owner_id FROM bookings b JOIN venues v ON b.venue_id = v.id WHERE b.id = ?",
            [$id]
        );

        if (!$booking) {
            Session::flash('error', 'Booking not found.');
            $this->redirect(url('/bookings'));
        }

        if ($this->hasRole('venue_owner') && $booking['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/bookings'));
        }

        $service = new BookingReminderService();
        $result  = $service->sendForBooking($id);

        if ($result['success']) {
            ActivityLog::record(
                "Sent booking reminder for {$booking['booking_reference']}",
                'whatsapp',
                'Booking',
                $id
            );
            Session::flash('success', 'Booking reminder sent via WhatsApp.');
        } else {
            Session::flash('error', $result['error'] ?? 'Failed to send reminder.');
        }

        $this->redirect(url('/bookings/' . $id));
    }
}
