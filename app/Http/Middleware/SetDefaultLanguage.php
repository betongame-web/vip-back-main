<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetDefaultLanguage
{
    public function handle($request, Closure $next)
    {
        try {
            if (auth('api')->check() && auth('api')->user()) {
                app()->setLocale(auth('api')->user()->language ?? config('app.locale', 'en'));
            }
        } catch (\Throwable $e) {
            // Ignore auth / database / token errors for public requests
        }

        return $next($request);
    }
}