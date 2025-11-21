<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionConfigurationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            abort_unless(
                $user && $user->hasRole('admin'),
                403
            );
            return $next($request);
        });
    }

    /**
     * Show the subscription configuration page
     */
    public function show(): Response
    {
        $config = $this->getConfiguration();

        return Inertia::render('admin/SubscriptionConfiguration', [
            'configuration' => $config,
        ]);
    }

    /**
     * Get current configuration
     */
    public function getConfiguration(): JsonResponse
    {
        $config = [
            'renewal' => [
                'idempotency_ttl_days' => config('subscription.renewal.idempotency_ttl_days'),
                'max_attempts' => config('subscription.renewal.max_attempts'),
                'retry_schedule' => config('subscription.renewal.retry_schedule'),
            ],
            'rate_limiting' => [
                'hourly_limit' => config('subscription.rate_limiting.hourly_limit'),
                'daily_limit' => config('subscription.rate_limiting.daily_limit'),
            ],
            'failure_alerts' => [
                'enabled' => config('subscription.failure_alerts.enabled'),
                'email_recipients' => config('subscription.failure_alerts.email_recipients'),
                'slack_webhook' => !empty(config('subscription.failure_alerts.slack_webhook')),
                'pagerduty_key' => !empty(config('subscription.failure_alerts.pagerduty_key')),
            ],
        ];

        return response()->json($config);
    }

    /**
     * Update retry configuration
     */
    public function updateRetryConfiguration(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'idempotency_ttl_days' => 'required|integer|min:1|max:365',
                'max_attempts' => 'required|integer|min:1|max:10',
                'retry_schedule' => 'required|array|min:1',
                'retry_schedule.*' => 'required|integer|min:1|max:365',
            ],
            [],
            []
        );

        // Validate retry schedule is in ascending order
        $schedule = $validated['retry_schedule'];
        for ($i = 1; $i < count($schedule); $i++) {
            if ($schedule[$i] <= $schedule[$i - 1]) {
                return response()->json([
                    'message' => 'Retry schedule must be in ascending order',
                    'errors' => ['retry_schedule' => 'Values must be in ascending order'],
                ], 422);
            }
        }

        // Validate schedule has enough entries
        if (count($schedule) < $validated['max_attempts'] - 1) {
            return response()->json([
                'message' => 'Retry schedule must have at least max_attempts - 1 entries',
                'errors' => ['retry_schedule' => 'Not enough schedule entries'],
            ], 422);
        }

        // Update environment variables
        $this->updateEnvFile([
            'RENEWAL_IDEMPOTENCY_TTL_DAYS' => $validated['idempotency_ttl_days'],
            'RENEWAL_MAX_ATTEMPTS' => $validated['max_attempts'],
            'RENEWAL_RETRY_SCHEDULE' => implode(',', $schedule),
        ]);

        // Clear config cache
        Artisan::call('config:clear');

        return response()->json([
            'message' => 'Retry configuration updated successfully',
            'configuration' => [
                'idempotency_ttl_days' => $validated['idempotency_ttl_days'],
                'max_attempts' => $validated['max_attempts'],
                'retry_schedule' => $schedule,
            ],
        ]);
    }

    /**
     * Update rate limiting configuration
     */
    public function updateRateLimitConfiguration(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hourly_limit' => 'required|integer|min:1|max:100',
            'daily_limit' => 'required|integer|min:1|max:1000',
        ]);

        // Validate daily limit is greater than hourly limit
        if ($validated['daily_limit'] < $validated['hourly_limit']) {
            return response()->json([
                'message' => 'Daily limit must be greater than or equal to hourly limit',
                'errors' => ['daily_limit' => 'Must be >= hourly limit'],
            ], 422);
        }

        $this->updateEnvFile([
            'RENEWAL_HOURLY_LIMIT' => $validated['hourly_limit'],
            'RENEWAL_DAILY_LIMIT' => $validated['daily_limit'],
        ]);

        Artisan::call('config:clear');

        return response()->json([
            'message' => 'Rate limit configuration updated successfully',
            'configuration' => $validated,
        ]);
    }

    /**
     * Update environment file
     */
    private function updateEnvFile(array $updates): void
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($updates as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}

