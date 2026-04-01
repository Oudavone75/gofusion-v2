<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAppKey
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $provided_key = $request->header('X-API-Key');
        $env_key = env('API_KEY');
        if ($provided_key !== $env_key) {
            return $this->error(status: false, message: 'Unauthorized: Invalid App Key', code: 401);
        }
        return $next($request);
    }
}
