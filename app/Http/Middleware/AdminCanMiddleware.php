<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCanMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $adminId = session('admin_id');

        if (!$adminId) {
            return redirect()->route('admin.login');
        }

        // super_admin bypasses all permission checks (fast path via session).
        if (in_array(session('admin_role'), ['super_admin', 'superadmin'], true)) {
            return $next($request);
        }

        $admin = Admin::find($adminId);

        if (!$admin || !$admin->can($permission)) {
            abort(403, 'Accès refusé — permission manquante : ' . $permission);
        }

        return $next($request);
    }
}
