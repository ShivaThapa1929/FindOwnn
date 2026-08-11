<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Venue;
use App\Models\Subscription;
use App\Models\AuditLog;
use App\Models\ActivityLog;
use App\Services\ValidationService;

class VenueController extends Controller
{
    private Venue $venueModel;

    public function __construct()
    {
        parent::__construct();
        $this->venueModel = new Venue();
    }

    // ── List ─────────────────────────────────────────────────────
    public function index(Request $request): void
    {
        $page      = max(1, (int) $request->query('page', 1));
        $filter    = $request->query('status', 'all');
        $search    = $request->query('search', '');
        $city      = $request->query('city', '');
        $verified  = $request->query('verified', '');
        $sortBy    = $request->query('sort', 'newest');

        $ownerId = $this->hasRole('venue_owner') ? $this->user()['id'] : null;
        $result = $this->venueModel->getAllWithOwner($page, 20, $filter, $search, $city, $verified, $sortBy, $ownerId);

        // Get unique cities for filter
        $cities = $this->db->fetchAll(
            "SELECT DISTINCT city FROM venues WHERE deleted_at IS NULL ORDER BY city ASC"
        );

        $data = [
            'title'    => 'Manage Venues',
            'result'   => $result,
            'filter'   => $filter,
            'search'   => $search,
            'city'     => $city,
            'verified' => $verified,
            'sortBy'   => $sortBy,
            'cities'   => $cities,
            'success'  => Session::getFlash('success'),
            'error'    => Session::getFlash('error'),
        ];

        // If AJAX request, return only the partial HTML
        if ($request->isAjax() || $request->query('ajax') === '1') {
            // Render without layout - just the partial content
            extract($data, EXTR_SKIP);
            $viewPath = ROOT_PATH . '/views/venues/partial.php';
            if (file_exists($viewPath)) {
                require $viewPath;
            }
            exit;
        }

        $this->render('venues.index', $data);
    }

    // ── Show ──────────────────────────────────────────────────────
    public function show(Request $request): void
    {
        $id    = (int) $request->param('id');
        $venue = $this->venueModel->getWithOwner($id);

        if (!$venue) {
            Session::flash('error', 'Venue not found.');
            $this->redirect(url('/venues'));
        }

        // Owners can only view their own
        if ($this->hasRole('venue_owner') && $venue['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/venues'));
        }

        $images       = $this->venueModel->getImages($id);
        
        // Get courts with featured images (or first gallery image as fallback)
        $courts = $this->db->fetchAll(
            "SELECT c.*, 
                    COALESCE(
                        (SELECT image_path FROM court_images WHERE court_id = c.id AND image_type = 'featured' LIMIT 1),
                        (SELECT image_path FROM court_images WHERE court_id = c.id ORDER BY id ASC LIMIT 1)
                    ) as featured_image
             FROM courts c
             WHERE c.venue_id = ?
             ORDER BY c.name ASC",
            [$id]
        );
        
        $recentBooks  = $this->db->fetchAll(
            "SELECT b.*, u.name AS user_name, u.phone AS user_phone
             FROM bookings b JOIN users u ON b.user_id = u.id
             WHERE b.venue_id = ? ORDER BY b.created_at DESC LIMIT 10",
            [$id]
        );
        $bookingStats = $this->db->fetch(
            "SELECT COUNT(*) AS total,
                    SUM(status='confirmed') AS confirmed,
                    SUM(CASE WHEN payment_status='paid' THEN amount ELSE 0 END) AS revenue
             FROM bookings WHERE venue_id = ?",
            [$id]
        );

        $this->render('venues.show', [
            'title'        => 'Venue: ' . e($venue['name']),
            'venue'        => $venue,
            'images'       => $images,
            'courts'       => $courts,
            'recentBooks'  => $recentBooks,
            'bookingStats' => $bookingStats ?: [],
        ]);
    }

    // ── Admin: Approve ────────────────────────────────────────────
    public function approve(Request $request): void
    {
        $id    = (int) $request->param('id');
        $venue = $this->venueModel->findOrFail($id);
        $admin = $this->user();

        $this->venueModel->approve($id, $admin['id']);
        AuditLog::log('VENUE_APPROVED', 'Venue', $id, ['status' => $venue['verification_status']], ['status' => 'approved']);
        ActivityLog::record("Approved venue: {$venue['name']}", 'venue', 'Venue', $id);

        if ($request->isAjax()) {
            $this->json(['success' => true, 'message' => 'Venue approved.']);
        }

        Session::flash('success', "Venue '{$venue['name']}' approved successfully.");
        $this->redirect(url('/venues/' . $id));
    }

    // ── Admin: Reject ─────────────────────────────────────────────
    public function reject(Request $request): void
    {
        $id    = (int) $request->param('id');
        $notes = trim($request->input('notes', ''));
        $venue = $this->venueModel->findOrFail($id);

        $v = new ValidationService();
        $v->required($notes, 'notes', 'Rejection reason');
        $v->minLength($notes, 'notes', 10, 'Rejection reason');
        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            $this->redirect(url('/venues/' . $id));
        }

        $this->venueModel->reject($id, $this->user()['id'], $notes);
        AuditLog::log('VENUE_REJECTED', 'Venue', $id, [], ['notes' => $notes]);
        ActivityLog::record("Rejected venue: {$venue['name']}", 'venue', 'Venue', $id);

        Session::flash('success', "Venue '{$venue['name']}' rejected.");
        $this->redirect(url('/venues'));
    }

    // ── Admin: Suspend / Unsuspend ────────────────────────────────
    public function toggleStatus(Request $request): void
    {
        $id    = (int) $request->param('id');
        $venue = $this->venueModel->findOrFail($id);
        $new   = $venue['status'] === 'suspended' ? 'active' : 'suspended';

        $this->db->execute(
            "UPDATE venues SET status = ?, updated_at = ? WHERE id = ?",
            [$new, now(), $id]
        );

        AuditLog::log('VENUE_STATUS_TOGGLED', 'Venue', $id, ['status' => $venue['status']], ['status' => $new]);
        ActivityLog::record("Venue '{$venue['name']}' status changed to {$new}", 'venue', 'Venue', $id);

        Session::flash('success', "Venue status updated to {$new}.");
        $this->redirect(url('/venues/' . $id));
    }

    // ── Admin: Assign Badge ───────────────────────────────────────
    public function assignBadge(Request $request): void
    {
        $id     = (int) $request->param('id');
        $expiry = $request->input('badge_expires_at', '');
        $notes  = $request->input('notes', '');

        $v = new ValidationService();
        $v->required($expiry, 'badge_expires_at', 'Badge expiry date');
        $v->custom(
            $expiry === '' || strtotime($expiry) > time(),
            'badge_expires_at', 'Badge expiry date must be in the future.'
        );
        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            $this->redirect(url('/venues/' . $id));
        }

        $venue = $this->venueModel->findOrFail($id);
        $this->venueModel->assignBadge($id, $this->user()['id'], $expiry, $notes);
        AuditLog::log('BADGE_ASSIGNED', 'Venue', $id, [], ['expires_at' => $expiry]);
        ActivityLog::record("Assigned verified badge to: {$venue['name']}", 'venue', 'Venue', $id);

        Session::flash('success', 'Verified badge assigned successfully.');
        $this->redirect(url('/venues/' . $id));
    }

    // ── Admin: Remove Badge ───────────────────────────────────────
    public function removeBadge(Request $request): void
    {
        $id    = (int) $request->param('id');
        $venue = $this->venueModel->findOrFail($id);

        $this->venueModel->removeBadge($id);
        AuditLog::log('BADGE_REMOVED', 'Venue', $id);
        ActivityLog::record("Removed verified badge from: {$venue['name']}", 'venue', 'Venue', $id);

        Session::flash('success', 'Verified badge removed.');
        $this->redirect(url('/venues/' . $id));
    }

    // ── Owner: Create Form ────────────────────────────────────────
    public function create(Request $request): void
    {
        $ownerId  = $this->user()['id'];
        $sub      = (new Subscription())->getActiveByUser($ownerId);
        $myCount  = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM venues WHERE owner_id = ? AND deleted_at IS NULL",
            [$ownerId]
        );
        $maxVenues = $sub ? (int)($sub['max_venues'] ?? 1) : 1;

        // Enforce plan limit
        if ($this->hasRole('venue_owner') && $myCount >= $maxVenues) {
            Session::flash('error',
                "Your {$sub['plan_name']} plan allows up to {$maxVenues} venue(s). " .
                "Upgrade your plan to add more venues."
            );
            $this->redirect(url('/venues'));
        }

        $old    = $_SESSION['old_input'] ?? [];
        $errors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        $this->render('venues.create', [
            'title'  => 'Add New Venue',
            'old'    => $old,
            'errors' => $errors,
        ]);
    }

    // ── Owner: Store ──────────────────────────────────────────────
    public function store(Request $request): void
    {
        $owner = $this->user();

        // Validate
        $v = new ValidationService();
        $v->required($request->input('name'), 'name', 'Venue name')
          ->minLength($request->input('name'), 'name', 3, 'Venue name')
          ->maxLength($request->input('name'), 'name', 200, 'Venue name')
          ->required($request->input('address'), 'address', 'Address')
          ->required($request->input('city'), 'city', 'City')
          ->required($request->input('state'), 'state', 'State')
          ->pincode($request->input('pincode', ''))
          ->required($request->input('price_per_hour'), 'price_per_hour', 'Price per hour')
          ->numeric($request->input('price_per_hour'), 'price_per_hour', 'Price per hour')
          ->min($request->input('price_per_hour'), 'price_per_hour', 1, 'Price per hour')
          ->max($request->input('price_per_hour'), 'price_per_hour', 100000, 'Price per hour')
          ->url($request->input('google_map_link', ''), 'google_map_link', 'Google Maps link');

        if ($v->fails()) {
            $v->flashAndRedirect(url('/venues/create'));
        }

        $name = trim($request->input('name'));
        $data = [
            'owner_id'            => $owner['id'],
            'name'                => $name,
            'slug'                => $this->uniqueSlug(slugify($name)),
            'description'         => substr(trim($request->raw('description', '')), 0, 2000),
            'address'             => $request->input('address'),
            'city'                => $request->input('city'),
            'state'               => $request->input('state'),
            'pincode'             => $request->input('pincode'),
            'google_map_link'     => $request->input('google_map_link'),
            'amenities'           => $this->parseAmenities($request->input('amenities', '')),
            'price_per_hour'      => round((float)$request->input('price_per_hour'), 2),
            'status'              => in_array($this->user()['role'], ['super_admin', 'admin']) ? 'active' : 'inactive',
            'verification_status' => in_array($this->user()['role'], ['super_admin', 'admin']) ? 'approved' : 'pending',
            'is_verified'         => in_array($this->user()['role'], ['super_admin', 'admin']) ? 1 : 0,
        ];

        $id = $this->venueModel->create($data);
        ActivityLog::record("Created new venue: {$name}", 'venue', 'Venue', $id);
        AuditLog::log('VENUE_CREATED', 'Venue', $id, [], $data);

        Session::flash('success', 'Venue submitted for review. Admin will verify within 24 hours.');
        $this->redirect(url('/venues/' . $id));
    }

    // ── Owner/Admin: Edit Form ────────────────────────────────────
    public function edit(Request $request): void
    {
        $id    = (int) $request->param('id');
        $venue = $this->venueModel->findOrFail($id);

        if ($this->hasRole('venue_owner') && $venue['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'You do not have permission to edit this venue.');
            $this->redirect(url('/venues'));
        }

        $old    = $_SESSION['old_input'] ?? [];
        $errors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        $this->render('venues.edit', [
            'title'  => 'Edit Venue',
            'venue'  => $venue,
            'old'    => $old,
            'errors' => $errors,
        ]);
    }

    // ── Owner/Admin: Update ───────────────────────────────────────
    public function update(Request $request): void
    {
        $id    = (int) $request->param('id');
        $venue = $this->venueModel->findOrFail($id);

        if ($this->hasRole('venue_owner') && $venue['owner_id'] != $this->user()['id']) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/venues'));
        }

        $v = new ValidationService();
        $v->required($request->input('name'), 'name', 'Venue name')
          ->minLength($request->input('name'), 'name', 3, 'Venue name')
          ->required($request->input('address'), 'address', 'Address')
          ->required($request->input('city'), 'city', 'City')
          ->required($request->input('state'), 'state', 'State')
          ->pincode($request->input('pincode', ''))
          ->required($request->input('price_per_hour'), 'price_per_hour', 'Price per hour')
          ->numeric($request->input('price_per_hour'), 'price_per_hour', 'Price per hour')
          ->min($request->input('price_per_hour'), 'price_per_hour', 1, 'Price per hour')
          ->url($request->input('google_map_link', ''), 'google_map_link', 'Google Maps link');

        if ($v->fails()) {
            $v->flashAndRedirect(url('/venues/' . $id . '/edit'));
        }

        $data = [
            'name'            => trim($request->input('name')),
            'description'     => substr(trim($request->raw('description', '')), 0, 2000),
            'address'         => $request->input('address'),
            'city'            => $request->input('city'),
            'state'           => $request->input('state'),
            'pincode'         => $request->input('pincode'),
            'google_map_link' => $request->input('google_map_link'),
            'amenities'       => $this->parseAmenities($request->input('amenities', '')),
            'price_per_hour'  => round((float)$request->input('price_per_hour'), 2),
        ];

        $this->venueModel->update($id, $data);
        AuditLog::log('VENUE_UPDATED', 'Venue', $id, $venue, $data);
        ActivityLog::record("Updated venue: {$venue['name']}", 'venue', 'Venue', $id);

        Session::flash('success', 'Venue updated successfully.');
        $this->redirect(url('/venues/' . $id));
    }

    // ── Admin: Delete ─────────────────────────────────────────────
    public function destroy(Request $request): void
    {
        $id    = (int) $request->param('id');
        $venue = $this->venueModel->findOrFail($id);

        $this->venueModel->softDelete($id);
        AuditLog::log('VENUE_DELETED', 'Venue', $id);
        ActivityLog::record("Deleted venue: {$venue['name']}", 'venue', 'Venue', $id);

        Session::flash('success', 'Venue deleted.');
        $this->redirect(url('/venues'));
    }

    // ── Helpers ───────────────────────────────────────────────────
    private function parseAmenities(string $raw): string
    {
        $items = array_filter(
            array_map('trim', explode(',', $raw)),
            fn($i) => $i !== ''
        );
        return json_encode(array_values($items));
    }

    private function uniqueSlug(string $slug): string
    {
        $base    = $slug;
        $counter = 1;
        while ($this->db->fetchColumn("SELECT COUNT(*) FROM venues WHERE slug = ?", [$slug])) {
            $slug = $base . '-' . $counter++;
        }
        return $slug;
    }
}
