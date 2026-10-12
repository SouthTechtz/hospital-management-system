<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces compulsory TOTP MFA (Part B): users whose roles require MFA must
 * enrol before reaching application screens and must verify each session.
 */
class EnsureMfaVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        // Users that have not enrolled yet are sent to the enrolment screen.
        if ($user->requiresMfa() && ! $user->mfa_enabled) {
            return redirect()->route('mfa.setup');
        }

        // Enrolled users must prove possession once per session.
        if ($user->mfa_enabled && ! $request->session()->get('hms.mfa_verified', false)) {
            return redirect()->route('mfa.challenge');
        }

        return $next($request);
    }
}