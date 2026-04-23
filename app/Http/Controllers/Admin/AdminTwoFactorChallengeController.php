<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class AdminTwoFactorChallengeController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (!session('admin_2fa_pending_id')) {
            return redirect()->route('admin.login');
        }

        return view('admin.two-factor-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $pendingAdminId = session('admin_2fa_pending_id');
        $admin = $pendingAdminId ? Admin::find($pendingAdminId) : null;

        if (!$admin || !$admin->two_factor_enabled || !$admin->two_factor_secret) {
            session()->forget('admin_2fa_pending_id');

            return redirect()->route('admin.login')->withErrors(['email' => 'Session 2FA invalide.']);
        }

        $secret = Crypt::decryptString($admin->two_factor_secret);
        if (!app(Google2FA::class)->verifyKey($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Code 2FA invalide.']);
        }

        session()->regenerate();
        session([
            'admin_id' => $admin->id_admin,
            'admin_pseudo' => $admin->pseudo_admin,
            'admin_role' => $admin->role,
            'admin_2fa_passed' => true,
        ]);
        session()->forget('admin_2fa_pending_id');

        return redirect()->route('admin.dashboard');
    }
}
