<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileCompletion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only check for authenticated users with specific roles
        if (!$user || !$user->hasAnyRole(['patient', 'employee', 'agent'])) {
            return $next($request);
        }

        // Only check on dashboard routes
        $routeName = $request->route()->getName();
        $dashboardRoutes = [
            'patient.dashboard',
            'employee.dashboard',
            'agent.dashboard'
        ];

        if (!in_array($routeName, $dashboardRoutes)) {
            return $next($request);
        }

        // Check if profile is incomplete (forced completion)
        if (!$user->isProfileComplete()) {
            // Add profile completion data to the request
            $request->merge([
                'show_profile_completion_modal' => true,
                'missing_profile_fields' => $user->getMissingProfileFields()
            ]);
        }

        return $next($request);
    }
}
