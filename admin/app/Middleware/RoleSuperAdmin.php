<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class RoleSuperAdmin
{
    public function handle(Request $request, callable $next): void
    {
        if ((Session::get('user')['role'] ?? '') !== 'super_admin') {
            Session::flash('error', 'Access denied. Super Admin only.');
            redirect(url('/dashboard'));
        }
        $next();
    }
}
