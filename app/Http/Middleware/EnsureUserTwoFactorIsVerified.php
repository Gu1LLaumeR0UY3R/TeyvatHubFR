<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserTwoFactorIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->two_factor_enabled) {
            return $next($request);
        }

        if ($request->session()->get('user_2fa_passed') === true) {
            return $next($request);
        }

        return redirect()->route('twofactor.challenge');
    }
}
