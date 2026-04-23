<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminTwoFactorIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = session('admin_id');
        if (!$adminId) {
            return redirect()->route('admin.login');
        }

        if (session('admin_role') === 'super_admin' && session('admin_2fa_passed') === true) {
            return $next($request);
        }

        $admin = Admin::find($adminId);
        if (!$admin || !$admin->two_factor_enabled) {
            return $next($request);
        }

        if (session('admin_2fa_passed') === true) {
            return $next($request);
        }

        session(['admin_2fa_pending_id' => $adminId]);

        return redirect()->route('admin.twofactor.challenge');
    }
}
