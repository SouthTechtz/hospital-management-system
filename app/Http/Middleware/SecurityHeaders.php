<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers required by Part B: CSP without inline scripts,
 * X-Frame-Options, Referrer-Policy, Permissions-Policy, HSTS over HTTPS.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-Frame-Options', 'DENY');
            $response->header('Referrer-Policy', 'same-origin');
            $response->header(
                'Permissions-Policy',
                'camera=(), microphone=(), geolocation=(), payment=(), interest-cohort=()'
            );
            $response->header(
                'Content-Security-Policy',
                $this->csp()
            );

            if ($request->isSecure()) {
                $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }
        }

        return $response;
    }

    private function csp(): string
    {
        $nonce = app('hms.csp.nonce');

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'",
            "style-src 'self'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
    }
}