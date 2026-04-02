<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
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
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $admin = Admin::where('email_admin', $request->email)->first();

        if (!$admin) {
            return back()->withErrors(['email' => 'Identifiants incorrects.'])->withInput();
        }

        try {
            $validPassword = Hash::check($request->password, $admin->mot_de_passe_admin);
        } catch (RuntimeException $exception) {
            // If a legacy/plain hash is stored, fail login gracefully instead of throwing a 500.
            $validPassword = false;
        }

        if (! $validPassword) {
            return back()->withErrors(['email' => 'Identifiants incorrects.'])->withInput();
        }

        session()->regenerate();
        session(['admin_id' => $admin->id_admin, 'admin_pseudo' => $admin->pseudo_admin]);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        session()->forget(['admin_id', 'admin_pseudo']);
        session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
