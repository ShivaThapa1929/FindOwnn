<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class RoleAdmin
{
    public function handle(Request $request, callable $next): void
    {
        $role = Session::get('user')['role'] ?? '';
        if (!in_array($role, ['super_admin', 'admin'])) {
            Session::flash('error', 'Access denied. Admin privileges required.');
            redirect(url('/dashboard'));
        }
        $next();
    }
}
