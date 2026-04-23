<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        $secret = (string) $request->session()->get('user_2fa_secret_temp', '');

        if ($secret === '') {
            return redirect()->route('profile.edit')
                ->withErrors(['code' => 'Session expirée. Regénère le QR code.']);
        }

        if (!app(Google2FA::class)->verifyKey($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Code 2FA invalide.']);
        }

        $user->update([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        $request->session()->forget('user_2fa_secret_temp');
        $request->session()->put('user_2fa_passed', true);

        return redirect()->route('profile.edit')->with('status', 'twofactor-enabled');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $request->user()->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
        ]);

        $request->session()->forget(['user_2fa_secret_temp']);
        $request->session()->put('user_2fa_passed', true);

        return redirect()->route('profile.edit')->with('status', 'twofactor-disabled');
    }
}
