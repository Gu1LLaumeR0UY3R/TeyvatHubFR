<?php

namespace App\Http\Controllers;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $qrCodeSvg = null;

        if ($user && !$user->two_factor_enabled) {
            $tempSecret = (string) $request->session()->get('user_2fa_secret_temp', '');
            if ($tempSecret === '') {
                $tempSecret = app(Google2FA::class)->generateSecretKey();
                $request->session()->put('user_2fa_secret_temp', $tempSecret);
            }

            $renderer = new ImageRenderer(new RendererStyle(220), new SvgImageBackEnd());
            $writer = new Writer($renderer);
            $qrCodeSvg = $writer->writeString(
                app(Google2FA::class)->getQRCodeUrl(config('app.name', 'TeyvatHub'), $user->email, $tempSecret)
            );
        }

        return view('profile.edit', [
            'user' => $user,
            'qrCodeSvg' => $qrCodeSvg,
            'manualSecret' => $request->session()->get('user_2fa_secret_temp'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
