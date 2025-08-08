<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware for install routes
        if ($request->is('install*')) {
            return $next($request);
        }

        // Check if installation is complete
        if (!file_exists(storage_path('app/installed.lock'))) {
            return redirect('/install');
        }

        // Check if essential configuration is missing
        try {
            // Try to access config that requires proper Laravel setup
            config('app.key');
            
            // If APP_KEY is missing or invalid, redirect to install
            if (empty(config('app.key'))) {
                return redirect('/install');
            }
            
        } catch (\Exception $e) {
            // If there are configuration errors, redirect to install
            return redirect('/install');
        }

        return $next($request);
    }
}