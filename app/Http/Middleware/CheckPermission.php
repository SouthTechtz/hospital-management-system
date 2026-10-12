<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level permission gate: `check.permission:patient.view`.
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        foreach ($permissions as $permission) {
            if ($user && $user->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, __('errors.not_authorized'));
    }
}