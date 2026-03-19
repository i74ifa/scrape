<?php

namespace App\Http\Middleware;

use App\Services\Currency;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppCustomizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (user()) {
            app()->setLocale(user('local'));
            app(Currency::class)->setCurrency(user('currency'));
        }
        return $next($request);
    }
}
