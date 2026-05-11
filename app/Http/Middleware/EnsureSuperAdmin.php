<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array((string) session('admin_role'), ['super_admin', 'superadmin'], true)) {
            abort(403, 'Accès refusé — super admin requis.');
        }

        return $next($request);
    }
}
