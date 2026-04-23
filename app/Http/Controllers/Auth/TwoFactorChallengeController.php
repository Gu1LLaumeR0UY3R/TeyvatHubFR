<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (!$user || !$user->two_factor_enabled) {
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        if (!$user || !$user->two_factor_enabled || !$user->two_factor_secret) {
            return redirect()->intended(route('dashboard'));
        }

        $secret = Crypt::decryptString($user->two_factor_secret);

        if (!app(Google2FA::class)->verifyKey($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Code 2FA invalide.']);
        }

        $request->session()->put('user_2fa_passed', true);

        return redirect()->intended(route('dashboard'));
    }
}
