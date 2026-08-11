<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

/** GuestMiddleware — Redirects authenticated users away from login/register */
class GuestMiddleware
{
    public function handle(Request $request, callable $next): void
    {
        if (Session::has('user')) {
            redirect(url('/dashboard'));
        }
        $next();
    }
}
