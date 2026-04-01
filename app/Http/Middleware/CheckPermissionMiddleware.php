<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission, $guard = null): Response
    {
        $guard = $guard ?? $this->getActiveGuard();

        $user = auth($guard)->user();

        if (!$user) {
            return redirect()->route($guard . '.login')
                ->withErrors(['message' => 'Please login to continue.']);
        }

        $hasDirectPermission = $user->getDirectPermissions()->contains('name', $permission);

        if (!$hasDirectPermission) {
            abort_unless($hasDirectPermission, 403);
        }

        return $next($request);
    }

    /**
     * Try to automatically detect the currently logged-in guard.
     */
    private function getActiveGuard()
    {
        foreach (array_keys(config('auth.guards')) as $guard) {
            if (auth($guard)->check()) {
                return $guard;
            }
        }

        // Default fallback
        return config('auth.defaults.guard');
    }
}
