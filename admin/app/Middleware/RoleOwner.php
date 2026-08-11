<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class RoleOwner
{
    public function handle(Request $request, callable $next): void
    {
        $role = Session::get('user')['role'] ?? '';
        if (!in_array($role, ['super_admin', 'admin', 'venue_owner'])) {
            Session::flash('error', 'Access denied.');
            redirect(url('/dashboard'));
        }
        $next();
    }
}
