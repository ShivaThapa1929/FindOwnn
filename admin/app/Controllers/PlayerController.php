<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\BookingReminderService;

class PlayerController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    public function index(Request $request): void
    {
        $page   = (int) $request->query('page', 1);
        $search = trim($request->query('search', ''));
        $filter = $request->query('filter', 'all');

        $ownerId = $this->hasRole('venue_owner') ? (int) $this->user()['id'] : null;

        $result = $this->userModel->getPlayers($page, 20, $search, $filter, $ownerId);

        $this->render('players.index', [
            'title'   => 'Players',
            'result'  => $result,
            'search'  => $search,
            'filter'  => $filter,
            'success' => Session::getFlash('success'),
            'error'   => Session::getFlash('error'),
        ]);
    }

    public function show(Request $request): void
    {
        $id     = (int) $request->param('id');
        $player = $this->userModel->findPlayer($id);

        if (!$player) {
            Session::flash('error', 'Player not found.');
            $this->redirect(url('/players'));
        }

        $ownerId = $this->hasRole('venue_owner') ? (int) $this->user()['id'] : null;
        if ($ownerId && !$this->userModel->playerBelongsToOwner($id, $ownerId)) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/players'));
        }

        $stats           = $this->userModel->getPlayerStats($id, $ownerId);
        $bookings        = $this->userModel->getPlayerBookings($id, 50, $ownerId);
        $whatsappHistory = $this->userModel->getPlayerWhatsAppHistory($player['phone'], $player['whatsapp_number']);

        $this->render('players.show', [
            'title'           => 'Player: ' . e($player['name']),
            'player'          => $player,
            'stats'           => $stats,
            'bookings'        => $bookings,
            'whatsappHistory' => $whatsappHistory,
        ]);
    }

    public function sendReminder(Request $request): void
    {
        $id        = (int) $request->param('id');
        $bookingId = (int) $request->input('booking_id', 0);

        $player = $this->userModel->findPlayer($id);
        if (!$player) {
            Session::flash('error', 'Player not found.');
            $this->redirect(url('/players'));
        }

        $ownerId = $this->hasRole('venue_owner') ? (int) $this->user()['id'] : null;
        if ($ownerId && !$this->userModel->playerBelongsToOwner($id, $ownerId)) {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/players'));
        }

        if (!$bookingId) {
            $upcoming = $this->userModel->getPlayerUpcomingBooking($id, $ownerId);
            $bookingId = $upcoming ? (int) $upcoming['id'] : 0;
        }

        if (!$bookingId) {
            Session::flash('error', 'No upcoming booking found for this player.');
            $this->redirect(url('/players/' . $id));
        }

        $service = new BookingReminderService();
        $result  = $service->sendForBooking($bookingId);

        if ($result['success']) {
            ActivityLog::record(
                "Sent booking reminder to {$player['name']} (booking #{$bookingId})",
                'whatsapp',
                'Booking',
                $bookingId
            );
            Session::flash('success', 'Booking reminder sent via WhatsApp.');
        } else {
            Session::flash('error', $result['error'] ?? 'Failed to send reminder.');
        }

        $redirect = $request->input('redirect', '');
        if ($redirect === 'booking') {
            $this->redirect(url('/bookings/' . $bookingId));
        }

        $this->redirect(url('/players/' . $id));
    }
}
