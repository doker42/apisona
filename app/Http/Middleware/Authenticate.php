<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Authenticate extends Middleware
{

    /**
     * @param $request
     * @param  \Closure  $next
     * @param ...$guards
     * @return mixed
     * @throws AuthenticationException
     */
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

        $request->headers->set('Accept', 'application/json');

        $this->authenticate($request, $guards);

        return $next($request);
    }


    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     * @return JsonResponse|null
     */
    protected function redirectTo(Request $request)
    {
        if (!$request->expectsJson()) {

            return response()->json([
                'message' => __('auth.required')
            ], 401);
        }
    }

}
