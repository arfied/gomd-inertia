<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limit subscription renewal requests to prevent abuse
 *
 * Limits:
 * - 5 renewal attempts per hour per user
 * - 20 renewal attempts per day per user
 */
class RateLimitSubscriptionRenewal
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Get configurable rate limits
        $hourlyLimit = (int) config('subscription.rate_limiting.hourly_limit', 5);
        $dailyLimit = (int) config('subscription.rate_limiting.daily_limit', 20);

        // Rate limit: hourly per user
        $hourlyKey = "renewal:hourly:{$user->id}";
        if (RateLimiter::tooManyAttempts($hourlyKey, $hourlyLimit)) {
            return response()->json([
                'error' => 'Too many renewal attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($hourlyKey),
            ], 429);
        }

        RateLimiter::hit($hourlyKey, 60); // 60 seconds = 1 minute decay

        // Rate limit: daily per user
        $dailyKey = "renewal:daily:{$user->id}";
        if (RateLimiter::tooManyAttempts($dailyKey, $dailyLimit)) {
            return response()->json([
                'error' => 'Daily renewal limit exceeded. Please try again tomorrow.',
                'retry_after' => RateLimiter::availableIn($dailyKey),
            ], 429);
        }

        RateLimiter::hit($dailyKey, 86400); // 86400 seconds = 1 day decay

        return $next($request);
    }
}

