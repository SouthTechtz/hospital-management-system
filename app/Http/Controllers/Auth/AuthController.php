<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly TotpService $totp,
    ) {
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['username', 'password']);
        $username = (string) $request->string('username');

        $this->ensureNotLocked($username, $request->ip());

        $authenticated = Auth::attempt($credentials, false);

        if (! $authenticated) {
            $this->registerFailure($username, $request->ip());

            $this->audit->log(
                AuditAction::LoginFailed,
                description: 'Failed login for username',
                newValues: ['username' => $username],
            );

            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active || $user->deactivated_at !== null || $user->trashed()) {
            Auth::logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages([
                'username' => __('auth.account_deactivated'),
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('hms.login_at', now()->toIso8601String());
        $request->session()->put('hms.last_activity', now()->toIso8601String());
        $request->session()->put('hms.mfa_verified', false);

        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        RateLimiter::clear($this->throttleKey($username, $request->ip()));

        $this->audit->log(
            AuditAction::Login,
            actor: $user,
            description: 'User signed in',
        );

        if ($user->requiresMfa() && ! $user->mfa_enabled) {
            return redirect()->route('mfa.setup');
        }

        if ($user->mfa_enabled) {
            return redirect()->route('mfa.challenge');
        }

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $this->audit->log(AuditAction::Logout, actor: $user, description: 'User signed out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function ensureNotLocked(string $username, ?string $ip): void
    {
        $user = User::where('username', $username)->first();

        if ($user && $user->locked_until && $user->locked_until->isFuture()) {
            $seconds = (int) $user->locked_until->diffInSeconds(now());

            throw ValidationException::withMessages([
                'username' => __('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        if (RateLimiter::tooManyAttempts($this->throttleKey($username, $ip), config('hms.password.max_attempts', 5))) {
            $seconds = RateLimiter::availableIn($this->throttleKey($username, $ip));
            $lockUntil = now()->addSeconds($seconds);

            if ($user) {
                $user->forceFill([
                    'failed_login_attempts' => config('hms.password.max_attempts', 5),
                    'locked_until' => $lockUntil,
                ])->save();
            }

            throw ValidationException::withMessages([
                'username' => __('auth.throttle', ['seconds' => $seconds]),
            ]);
        }
    }

    private function registerFailure(string $username, ?string $ip): void
    {
        $user = User::where('username', $username)->first();

        if ($user) {
            $attempts = (int) $user->failed_login_attempts + 1;
            $max = (int) config('hms.password.max_attempts', 5);

            $user->forceFill([
                'failed_login_attempts' => $attempts,
                'locked_until' => $attempts >= $max ? now()->addMinutes((int) config('hms.password.lockout_minutes', 15)) : $user->locked_until,
            ])->save();
        }

        RateLimiter::hit($this->throttleKey($username, $ip), (int) config('hms.password.lockout_minutes', 15) * 60);
    }

    private function throttleKey(string $username, ?string $ip): string
    {
        return 'login:'.mb_strtolower($username).'|'.(string) $ip;
    }
}