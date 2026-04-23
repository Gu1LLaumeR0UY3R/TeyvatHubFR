<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class AdminTwoFactorController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        $admin = Admin::find(session('admin_id'));
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $qrCodeSvg = null;
        if (!$admin->two_factor_enabled) {
            $tempSecret = (string) $request->session()->get('admin_2fa_secret_temp', '');
            if ($tempSecret === '') {
                $tempSecret = app(Google2FA::class)->generateSecretKey();
                $request->session()->put('admin_2fa_secret_temp', $tempSecret);
            }

            $qrCodeSvg = $this->buildQrCode(
                app(Google2FA::class)->getQRCodeUrl(config('app.name', 'TeyvatHub').'-admin', $admin->email_admin, $tempSecret)
            );
        }

        return view('admin.security.two-factor', [
            'adminUser' => $admin,
            'qrCodeSvg' => $qrCodeSvg,
            'manualSecret' => $request->session()->get('admin_2fa_secret_temp'),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $admin = Admin::findOrFail(session('admin_id'));
        $secret = (string) $request->session()->get('admin_2fa_secret_temp', '');

        if ($secret === '') {
            return redirect()->route('admin.twofactor.settings')
                ->withErrors(['code' => 'Session expirée. Regénère le QR code.']);
        }

        if (!app(Google2FA::class)->verifyKey($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Code 2FA invalide.']);
        }

        $admin->update([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        $request->session()->forget('admin_2fa_secret_temp');
        $request->session()->put('admin_2fa_passed', true);

        return redirect()->route('admin.twofactor.settings')->with('success', '2FA activée.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required'],
        ]);

        $admin = Admin::findOrFail(session('admin_id'));

        if (!Hash::check($request->input('password'), $admin->mot_de_passe_admin)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        $admin->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
        ]);

        $request->session()->forget('admin_2fa_secret_temp');
        $request->session()->put('admin_2fa_passed', true);

        return redirect()->route('admin.twofactor.settings')->with('success', '2FA désactivée.');
    }

    private function buildQrCode(string $content): string
    {
        $renderer = new ImageRenderer(new RendererStyle(220), new SvgImageBackEnd());
        $writer = new Writer($renderer);

        return $writer->writeString($content);
    }
}
