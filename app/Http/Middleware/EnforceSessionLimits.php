<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces the security session policy: idle timeout, absolute timeout and a
 * concurrent-session limit (Part B). Times are configurable via config/hms.php
 * and the environment.
 */
class EnforceSessionLimits
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $session = $request->session();
        $idleMinutes = (int) config('hms.session.idle_minutes', 15);
        $absoluteHours = (int) config('hms.session.absolute_hours', 12);
        $limit = (int) config('hms.session.concurrent_limit', 3);

        $loginAt = $session->get('hms.login_at');
        $lastActivity = $session->get('hms.last_activity');

        if ($loginAt === null) {
            $session->put('hms.login_at', now()->toIso8601String());
        } else {
            $loginTime = \Carbon\Carbon::parse($loginAt);

            if (now()->diffInMinutes($loginTime, false) > $absoluteHours * 60) {
                $this->endSession($request, 'absolute_timeout');

                return $this->expired($request);
            }
        }

        if ($lastActivity !== null) {
            $last = \Carbon\Carbon::parse($lastActivity);

            if (now()->diffInMinutes($last, false) > $idleMinutes) {
                $this->endSession($request, 'idle_timeout');

                return $this->expired($request);
            }
        }

        $session->put('hms.last_activity', now()->toIso8601String());

        // Concurrent sessions: kill the oldest session record once the limit
        // is exceeded. This session is the newest, so it survives.
        if ($limit > 0 && $user->getKey()) {
            $sessionId = $session->getId();

            $activeCount = DB::table('sessions')
                ->where('user_id', $user->getKey())
                ->count();

            if ($activeCount > $limit) {
                $excess = $activeCount - $limit;

                $oldest = DB::table('sessions')
                    ->where('user_id', $user->getKey())
                    ->where('id', '!=', $sessionId)
                    ->orderBy('last_activity')
                    ->limit($excess)
                    ->pluck('id');

                DB::table('sessions')->whereIn('id', $oldest)->delete();
            }
        }

        return $next($request);
    }

    private function endSession(Request $request, string $reason): void
    {
        $user = $request->user();

        if ($user) {
            app(\App\Services\AuditLogger::class)->log(
                \App\Enums\AuditAction::Logout,
                actor: $user,
                description: 'Session ended: '.$reason,
            );
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function expired(Request $request): Response
    {
        return redirect()->guest(route('login'))
            ->with('status', __('auth.session_timeout'));
    }
}