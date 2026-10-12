<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Swahili/English locale from the logged-in user, falling back to the session
 * and the request (Part A: switchable per user, complete coverage).
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('hms.locale.supported', ['en', 'sw']);
        $locale = $request->session()->get('locale');

        if ($request->user()?->preferred_locale) {
            $locale = $request->user()->preferred_locale;
        }

        $locale = $locale && in_array($locale, $supported, true) ? $locale : config('hms.locale.default', 'en');

        App::setLocale($locale);
        $request->session()->put('locale', $locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}