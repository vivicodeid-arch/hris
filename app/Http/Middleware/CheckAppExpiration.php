<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAppExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningInConsole()) {
            return $next($request);
        }

        $setting = \App\Models\Pengaturanumum::where('id', 1)->first();
        if ($setting && $setting->expired) {
            $expiredDate = \Carbon\Carbon::parse($setting->expired)->endOfDay();
            if (\Carbon\Carbon::now()->gt($expiredDate)) {
                // Only block if the user is authenticated
                if (auth()->check()) {
                    // Allow master admin to access the application to extend the date
                    if (auth()->user()->hasRole('master admin')) {
                        return $next($request);
                    }

                    // Allow logout route
                    if ($request->is('logout') || $request->routeIs('logout')) {
                        return $next($request);
                    }

                    // Handle JSON / API requests
                    if ($request->expectsJson() || $request->is('api/*')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Aplikasi telah kadaluarsa. Silakan hubungi administrator.'
                        ], 403);
                    }

                    // Block other web requests
                    abort(403, 'Aplikasi telah kadaluarsa. Silakan hubungi administrator.');
                }
            }
        }

        return $next($request);
    }
}
