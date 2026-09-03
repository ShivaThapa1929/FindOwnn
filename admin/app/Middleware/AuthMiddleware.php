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

        // Venue Owner Dashboard Protection: Block unverified or suspended venue owners
        if (($user['role'] ?? '') === 'venue_owner') {
            $userId = (int)($user['id'] ?? 0);
            $dbUser = (new \App\Models\User())->find($userId);

            if (!$dbUser || $dbUser['status'] === 'suspended') {
                Session::destroy();
                Session::flash('error', 'Account suspended or invalid. Please contact support.');
                redirect(url('/owner/login'));
            }

            if (empty($dbUser['email_verified_at']) || $dbUser['status'] === 'pending_email_verification') {
                $_SESSION['unverified_email'] = $dbUser['email'];
                Session::flash('error', 'Please verify your email before logging in.');
                redirect(url('/owner/verify-notice?email=' . urlencode($dbUser['email'])));
            }
        }

        $next();
    }
}
