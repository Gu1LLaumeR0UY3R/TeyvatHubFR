<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use RuntimeException;

class AdminAuthController extends Controller
{
    public function login(): View|RedirectResponse
    {
        if (session('admin_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => trim((string) $request->input('email')),
            'password' => trim((string) $request->input('password')),
        ]);

        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $admin = Admin::where('email_admin', $request->email)->first();

        if (!$admin) {
            // Log tentative avec email inconnu
            ActivityLogService::log(
                action:    'admin_login_failed',
                level:     'warning',
                context:   ['email' => $request->email, 'reason' => 'email_not_found'],
                userLabel: $request->email,
                request:   $request,
            );
            if (ActivityLogService::isSuspiciousLogin($request->ip())) {
                ActivityLogService::log('suspicious_login_alert', 'critical', ['source' => 'admin', 'email' => $request->email], request: $request);
            }
            return back()->withErrors(['email' => 'Identifiants incorrects.'])->withInput();
        }

        try {
            $validPassword = Hash::check($request->password, $admin->mot_de_passe_admin);
        } catch (RuntimeException $exception) {
            // If a legacy/plain hash is stored, fail login gracefully instead of throwing a 500.
            $validPassword = false;
        }

        if (! $validPassword) {
            ActivityLogService::log(
                action:    'admin_login_failed',
                level:     'warning',
                context:   ['email' => $request->email, 'reason' => 'wrong_password'],
                userType:  'admin',
                userId:    $admin->id_admin,
                userLabel: $admin->email_admin,
                request:   $request,
            );
            if (ActivityLogService::isSuspiciousLogin($request->ip())) {
                ActivityLogService::log('suspicious_login_alert', 'critical', ['source' => 'admin', 'admin_id' => $admin->id_admin], request: $request);
            }
            return back()->withErrors(['email' => 'Identifiants incorrects.'])->withInput();
        }

        session()->regenerate();

        if ($admin->two_factor_enabled) {
            session([
                'admin_2fa_pending_id' => $admin->id_admin,
            ]);

            return redirect()->route('admin.twofactor.challenge');
        }

        session([
            'admin_id'    => $admin->id_admin,
            'admin_pseudo' => $admin->pseudo_admin,
            'admin_role'  => $admin->role,
            'admin_2fa_passed' => true,
        ]);

        ActivityLogService::log(
            action:    'admin_login_success',
            level:     'info',
            userType:  'admin',
            userId:    $admin->id_admin,
            userLabel: $admin->email_admin,
            request:   $request,
        );

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        session()->forget([
            'admin_id',
            'admin_pseudo',
            'admin_role',
            'admin_2fa_passed',
            'admin_2fa_pending_id',
        ]);
        session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
