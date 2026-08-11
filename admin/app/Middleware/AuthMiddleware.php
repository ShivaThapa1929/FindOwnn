<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

/**
 * AuthMiddleware — Ensures the user is logged in.
 * Redirects to login page if not authenticated.
 */
class AuthMiddleware
{
    public function handle(Request $request, callable $next): void
    {
        if (!Session::has('user')) {
            Session::flash('error', 'Please login to continue.');
            redirect(url('/owner/login'));
        }
        $next();
    }
}
