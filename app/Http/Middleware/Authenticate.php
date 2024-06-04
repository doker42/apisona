<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{

    public function handle($request, \Closure $next, ...$guards)
    {
        $excludedRoutes = config('auth.excluded_routes');

        $path = $request->path();

        if (in_array($path, $excludedRoutes)) {
            return $next($request);
        }

        foreach ($excludedRoutes as $route) {
            if (str_starts_with($path, $route)) {
                return $next($request);
            }
        }

        $this->authenticate($request, $guards);

        return $next($request);
    }


    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request)
    {
        if (! $request->expectsJson()) {

            return response()->json([
                'message' => __('auth.required')
            ], 401);
        }
    }
}
