<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if installation is complete
        if (!file_exists(storage_path('app/installed.lock'))) {
            // If not installed, redirect to installation
            return redirect('/install');
        }

        return $next($request);
    }
}
