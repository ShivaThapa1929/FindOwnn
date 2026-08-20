<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

/**
 * AuthMiddleware — Ensures the user is logged in and verified.
 * Blocks unverified venue owners from accessing dashboard routes.
 */
class AuthMiddleware
{
    public function handle(Request $request, callable $next): void
    {
        if (!Session::has('user')) {
            Session::flash('error', 'Please login to continue.');
            redirect(url('/owner/login'));
        }

        $user = Session::get('user');

        // Venue Owner Dashboard Protection: Block unverified venue owners
        if (($user['role'] ?? '') === 'venue_owner') {
            if (empty($user['email_verified_at']) || ($user['status'] ?? '') === 'pending_email_verification') {
                Session::flash('error', 'Please verify your email address before accessing your Venue Owner Dashboard.');
                redirect(url('/owner/verify-notice'));
            }
        }

        $next();
    }
}
