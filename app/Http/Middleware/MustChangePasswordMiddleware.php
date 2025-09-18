<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MustChangePasswordMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to authenticated users in the portal
        if (Auth::check() && Auth::user()->must_change_password) {
            // Skip this check for password change routes and logout
            $currentRoute = $request->route()?->getName();
            $skipRoutes = [
                'filament.portal.auth.logout',
                'filament.portal.pages.change-password',
            ];
            
            if (!in_array($currentRoute, $skipRoutes)) {
                return redirect()->route('filament.portal.pages.change-password');
            }
        }

        return $next($request);
    }
}
