<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Services\ValidationService;

class CourtController extends Controller
{
    // List courts for a venue
    public function index(Request $request): void
    {
        $venueId = (int) $request->query('venue_id', 0);
        
        if (!$venueId) {
            Session::flash('error', 'Venue not specified.');
            $this->redirect(url('/venues'));
        }

        // Get venue details
        $venue = $this->db->fetch(
            "SELECT v.*, u.name AS owner_name 
             FROM venues v 
             LEFT JOIN users u ON v.owner_id = u.id 
             WHERE v.id = ?",
            [$venueId]
        );

        if (!$venue) {
            Session::flash('error', 'Venue not found.');
            $this->redirect(url('/venues'));
        }

        // Check ownership for venue owners
        if ($this->hasRole('venue_owner') && $venue['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/venues'));
        }

        // Get courts for this venue
        $courts = $this->db->fetchAll(
            "SELECT c.*, s.name AS sport_name, s.slug AS sport_slug,
                    (SELECT COUNT(*) FROM court_images ci WHERE ci.court_id = c.id) AS image_count
             FROM courts c
             LEFT JOIN sports s ON c.sport_id = s.id
             WHERE c.venue_id = ? AND c.deleted_at IS NULL
             ORDER BY c.sort_order ASC, c.created_at DESC",
            [$venueId]
        );

        // Get available sports
        $sports = $this->db->fetchAll(
            "SELECT * FROM sports WHERE is_active = 1 ORDER BY sort_order ASC"
        );

        $this->render('courts.index', [
            'title'   => 'Manage Courts - ' . e($venue['name']),
            'venue'   => $venue,
            'courts'  => $courts,
            'sports'  => $sports,
            'success' => Session::getFlash('success'),
            'error'   => Session::getFlash('error'),
        ]);
    }


    // Create court
    public function store(Request $request): void
    {
        $venueId = (int) $request->input('venue_id', 0);
        
        // Verify venue ownership
        $venue = $this->db->fetch(
            "SELECT * FROM venues WHERE id = ? AND deleted_at IS NULL",
            [$venueId]
        );

        if (!$venue) {
            Session::flash('error', 'Venue not found.');
            $this->redirect(url('/venues'));
        }

        if ($this->hasRole('venue_owner') && $venue['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/venues'));
        }

        // Validation
        $v = new ValidationService();
        $v->required($request->input('name'), 'name', 'Court name')
          ->minLength($request->input('name', ''), 'name', 2, 'Court name')
          ->required($request->input('sport_id'), 'sport_id', 'Sport')
          ->required($request->input('price_per_hour'), 'price_per_hour', 'Price per hour')
          ->numeric($request->input('price_per_hour'), 'price_per_hour', 'Price per hour')
          ->min($request->input('price_per_hour'), 'price_per_hour', 1, 'Price per hour');

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            $this->redirect(url('/courts?venue_id=' . $venueId));
        }

        // Parse amenities
        $amenities = $request->input('amenities', '');
        $amenitiesArray = array_filter(array_map('trim', explode(',', $amenities)));
        
        // Parse equipment
        $equipment = $request->input('equipment', '');
        $equipmentArray = array_filter(array_map('trim', explode(',', $equipment)));

        // Insert court
        $courtId = $this->db->insert(
            "INSERT INTO courts 
            (venue_id, sport_id, name, court_number, description, surface_type, dimensions, 
             capacity, price_per_hour, amenities, equipment_provided, status, is_indoor, 
             has_lighting, booking_slot_duration, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $venueId,
                $request->input('sport_id'),
                $request->input('name'),
                $request->input('court_number', ''),
                $request->input('description', ''),
                $request->input('surface_type', ''),
                $request->input('dimensions', ''),
                $request->input('capacity', 0),
                $request->input('price_per_hour'),
                json_encode($amenitiesArray),
                json_encode($equipmentArray),
                'active',
                $request->input('is_indoor', 0),
                $request->input('has_lighting', 1),
                60,
                0
            ]
        );

        ActivityLog::record("Created court: {$request->input('name')} at venue #{$venueId}", 'court', 'Court', $courtId);
        
        Session::flash('success', 'Court created successfully.');
        $this->redirect(url('/courts?venue_id=' . $venueId));
    }


    // Delete court
    public function destroy(Request $request): void
    {
        $courtId = (int) $request->param('id');
        
        $court = $this->db->fetch(
            "SELECT c.*, v.owner_id 
             FROM courts c 
             JOIN venues v ON c.venue_id = v.id 
             WHERE c.id = ? AND c.deleted_at IS NULL",
            [$courtId]
        );

        if (!$court) {
            Session::flash('error', 'Court not found.');
            $this->redirect(url('/venues'));
        }

        if ($this->hasRole('venue_owner') && $court['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/venues'));
        }

        // Check for active bookings
        $activeBookings = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM bookings 
             WHERE court_id = ? AND status IN ('confirmed', 'pending') 
             AND booking_date >= CURDATE()",
            [$courtId]
        );

        if ($activeBookings > 0) {
            Session::flash('error', "Cannot delete court. It has {$activeBookings} active booking(s).");
            $this->redirect(url('/courts?venue_id=' . $court['venue_id']));
        }

        // Soft delete
        $this->db->execute(
            "UPDATE courts SET deleted_at = NOW() WHERE id = ?",
            [$courtId]
        );

        ActivityLog::record("Deleted court: {$court['name']}", 'court', 'Court', $courtId);
        
        Session::flash('success', 'Court deleted successfully.');
        $this->redirect(url('/courts?venue_id=' . $court['venue_id']));
    }

    // Update court status
    public function updateStatus(Request $request): void
    {
        $courtId = (int) $request->param('id');
        $status = $request->input('status');
        
        if (!in_array($status, ['active', 'inactive', 'maintenance'])) {
            Session::flash('error', 'Invalid status.');
            $this->redirect(url('/venues'));
        }

        $court = $this->db->fetch(
            "SELECT c.*, v.owner_id 
             FROM courts c 
             JOIN venues v ON c.venue_id = v.id 
             WHERE c.id = ?",
            [$courtId]
        );

        if (!$court) {
            Session::flash('error', 'Court not found.');
            $this->redirect(url('/venues'));
        }

        if ($this->hasRole('venue_owner') && $court['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/venues'));
        }

        $this->db->execute(
            "UPDATE courts SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $courtId]
        );

        ActivityLog::record("Updated court status to {$status}: {$court['name']}", 'court', 'Court', $courtId);

        if ($request->isAjax()) {
            $this->json(['success' => true, 'status' => $status]);
        }

        Session::flash('success', 'Court status updated.');
        $this->redirect(url('/courts?venue_id=' . $court['venue_id']));
    }

    // Edit court (show form)
    public function edit(Request $request): void
    {
        $courtId = (int) $request->param('id');

        $court = $this->db->fetch(
            "SELECT c.*, s.name as sport_name, v.name as venue_name, v.owner_id, v.id as venue_id
             FROM courts c
             LEFT JOIN sports s ON c.sport_id = s.id
             LEFT JOIN venues v ON c.venue_id = v.id
             WHERE c.id = ? AND c.deleted_at IS NULL",
            [$courtId]
        );

        if (!$court) {
            Session::flash('error', 'Court not found.');
            $this->redirect(url('/venues'));
        }

        // Check ownership for venue owners
        if ($this->hasRole('venue_owner') && $court['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/venues'));
        }

        // Get available sports
        $sports = $this->db->fetchAll(
            "SELECT * FROM sports WHERE is_active = 1 ORDER BY sort_order ASC"
        );

        // Parse amenities and equipment
        $amenitiesArray = [];
        if ($court['amenities']) {
            $decoded = json_decode($court['amenities'], true);
            $amenitiesArray = is_array($decoded) ? $decoded : [];
        }
        $court['amenities_string'] = implode(', ', $amenitiesArray);

        $equipmentArray = [];
        if ($court['equipment_provided']) {
            $decoded = json_decode($court['equipment_provided'], true);
            $equipmentArray = is_array($decoded) ? $decoded : [];
        }
        $court['equipment_string'] = implode(', ', $equipmentArray);

        $this->render('courts.edit', [
            'title' => 'Edit Court - ' . e($court['name']),
            'court' => $court,
            'sports' => $sports,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error'),
        ]);
    }

    // Update court
    public function update(Request $request): void
    {
        $courtId = (int) $request->param('id');
        
        $court = $this->db->fetch(
            "SELECT c.*, v.owner_id, v.id as venue_id
             FROM courts c 
             JOIN venues v ON c.venue_id = v.id 
             WHERE c.id = ? AND c.deleted_at IS NULL",
            [$courtId]
        );

        if (!$court) {
            Session::flash('error', 'Court not found.');
            $this->redirect(url('/venues'));
        }

        if ($this->hasRole('venue_owner') && $court['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/venues'));
        }

        // Validation
        $v = new ValidationService();
        $v->required($request->input('name'), 'name', 'Court name')
          ->minLength($request->input('name', ''), 'name', 2, 'Court name')
          ->required($request->input('sport_id'), 'sport_id', 'Sport')
          ->required($request->input('price_per_hour'), 'price_per_hour', 'Price per hour')
          ->numeric($request->input('price_per_hour'), 'price_per_hour', 'Price per hour')
          ->min($request->input('price_per_hour'), 'price_per_hour', 1, 'Price per hour');

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            $this->redirect(url('/courts/' . $courtId . '/edit'));
        }

        // Parse amenities
        $amenities = $request->input('amenities', '');
        $amenitiesArray = array_filter(array_map('trim', explode(',', $amenities)));
        
        // Parse equipment
        $equipment = $request->input('equipment', '');
        $equipmentArray = array_filter(array_map('trim', explode(',', $equipment)));

        // Update court
        $this->db->execute(
            "UPDATE courts SET 
                sport_id = ?, 
                name = ?, 
                court_number = ?, 
                description = ?, 
                surface_type = ?, 
                dimensions = ?, 
                capacity = ?, 
                price_per_hour = ?, 
                amenities = ?, 
                equipment_provided = ?, 
                is_indoor = ?, 
                has_lighting = ?, 
                updated_at = NOW()
             WHERE id = ?",
            [
                $request->input('sport_id'),
                $request->input('name'),
                $request->input('court_number', ''),
                $request->input('description', ''),
                $request->input('surface_type', ''),
                $request->input('dimensions', ''),
                $request->input('capacity', 0),
                $request->input('price_per_hour'),
                json_encode($amenitiesArray),
                json_encode($equipmentArray),
                $request->input('is_indoor', 0),
                $request->input('has_lighting', 1),
                $courtId
            ]
        );

        ActivityLog::record("Updated court: {$request->input('name')}", 'court', 'Court', $courtId);
        
        Session::flash('success', 'Court updated successfully.');
        $this->redirect(url('/courts?venue_id=' . $court['venue_id']));
    }

    // Show court images management page
    public function showImages(Request $request): void
    {
        $courtId = (int) $request->param('id');

        $court = $this->db->fetch(
            "SELECT c.*, s.name as sport_name, v.name as venue_name, v.owner_id, v.id as venue_id
             FROM courts c
             LEFT JOIN sports s ON c.sport_id = s.id
             LEFT JOIN venues v ON c.venue_id = v.id
             WHERE c.id = ? AND c.deleted_at IS NULL",
            [$courtId]
        );

        if (!$court) {
            Session::flash('error', 'Court not found.');
            $this->redirect(url('/venues'));
        }

        // Check ownership for venue owners
        if ($this->hasRole('venue_owner') && $court['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/venues'));
        }

        // Get all images for this court
        $images = $this->db->fetchAll(
            "SELECT * FROM court_images 
             WHERE court_id = ? 
             ORDER BY sort_order ASC, created_at DESC",
            [$courtId]
        );

        $this->render('courts.images', [
            'title' => 'Manage Court Images - ' . e($court['name']),
            'court' => $court,
            'images' => $images,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error'),
        ]);
    }

    // Dynamic API to retrieve active courts by venue ID
    public function apiGetCourts(Request $request): void
    {
        $venueId = (int) $request->query('venue_id', 0);
        if (!$venueId) {
            $this->json(['error' => 'Venue ID required'], 400);
        }

        $courts = $this->db->fetchAll(
            "SELECT id, name, court_number FROM courts 
             WHERE venue_id = ? AND deleted_at IS NULL AND status = 'active'
             ORDER BY court_number ASC",
            [$venueId]
        );

        $this->json(['courts' => $courts]);
    }
}
