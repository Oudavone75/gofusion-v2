<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLangMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $default_lang = $request->input('lang', 'fr');

        if ($request->user()) {
            $user = $request->user();
            $user_lang = $user->userDetails->language->label ?? $default_lang;

            app()->setLocale($user_lang);
        } else {

            app()->setLocale($default_lang);
        }

        return $next($request);
    }
}
