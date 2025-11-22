<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For JSON requests, return 401 instead of redirecting
        if ($request->expectsJson()) {
            return null;
        }

        return $request->routeIs('patient.*')
            ? route('patient.login')
            : route('login');
    }
}

