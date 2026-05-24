<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use App\Services\ActivityLogService;

class LoginRequest extends FormRequest
{
    /**
     * Normalize credentials before validation/authentication.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => trim((string) $this->input('email')),
            'password' => trim((string) $this->input('password')),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        try {
            $authenticated = Auth::attempt($this->only('email', 'password'), $this->boolean('remember'));
        } catch (RuntimeException $exception) {
            // Legacy hashes (or malformed stored values) must fail authentication without a 500.
            $authenticated = false;
        }

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            // Loguer la tentative échouée et détecter l'activité suspecte
            ActivityLogService::log(
                action:    'login_failed',
                level:     'warning',
                context:   ['email' => $this->input('email')],
                userType:  null,
                userId:    null,
                userLabel: $this->input('email'),
                request:   $this,
            );

            // Alerte critique si seuil atteint
            if (ActivityLogService::isSuspiciousLogin($this->ip())) {
                ActivityLogService::log(
                    action:  'suspicious_login_alert',
                    level:   'critical',
                    context: ['email' => $this->input('email'), 'threshold_reached' => true],
                    request: $this,
                );
            }

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
