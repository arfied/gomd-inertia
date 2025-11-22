<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FailedRenewalsController extends Controller
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
     * Get failed renewals for the admin dashboard widget
     */
    public function index(Request $request): JsonResponse
    {
        $days = (int) $request->query('days', 7);
        $limit = (int) $request->query('limit', 10);
        $since = now()->subDays($days);

        // Query the event store for failed renewal sagas
        $failedSagas = DB::table('event_store')
            ->where('event_type', 'SubscriptionRenewalSagaFailed')
            ->where('occurred_at', '>=', $since)
            ->orderBy('occurred_at', 'desc')
            ->limit($limit)
            ->get();

        $failedRenewals = $failedSagas->map(function ($saga) {
            $payload = json_decode($saga->event_data, true);
            $subscription = Subscription::find($payload['subscription_id'] ?? null);
            $user = $subscription ? $subscription->user : null;

            return [
                'saga_uuid' => $saga->aggregate_uuid,
                'subscription_id' => $payload['subscription_id'] ?? null,
                'user_id' => $payload['user_id'] ?? null,
                'user_name' => $user?->name ?? 'Unknown',
                'user_email' => $user?->email ?? 'Unknown',
                'amount' => $payload['amount'] ?? 0,
                'reason' => $payload['reason'] ?? 'Unknown',
                'error_message' => $payload['error_message'] ?? null,
                'failed_at' => $saga->occurred_at,
                'attempts_made' => $payload['attempts_made'] ?? 0,
            ];
        });

        // Get summary statistics
        $totalFailures = DB::table('event_store')
            ->where('event_type', 'SubscriptionRenewalSagaFailed')
            ->where('occurred_at', '>=', $since)
            ->count();

        $totalAmount = DB::table('event_store')
            ->where('event_type', 'SubscriptionRenewalSagaFailed')
            ->where('occurred_at', '>=', $since)
            ->get()
            ->sum(function ($saga) {
                $payload = json_decode($saga->event_data, true);
                return $payload['amount'] ?? 0;
            });

        return response()->json([
            'data' => $failedRenewals,
            'summary' => [
                'total_failures' => $totalFailures,
                'total_amount' => $totalAmount,
                'period_days' => $days,
                'period_start' => $since->toDateString(),
                'period_end' => now()->toDateString(),
            ],
        ]);
    }

    /**
     * Get a single failed renewal details
     */
    public function show(string $sagaUuid): JsonResponse
    {
        $saga = DB::table('event_store')
            ->where('aggregate_uuid', $sagaUuid)
            ->where('event_type', 'SubscriptionRenewalSagaFailed')
            ->first();

        if (!$saga) {
            return response()->json(['message' => 'Failed renewal not found'], 404);
        }

        $payload = json_decode($saga->event_data, true);
        $subscription = Subscription::find($payload['subscription_id'] ?? null);
        $user = $subscription ? $subscription->user : null;

        return response()->json([
            'saga_uuid' => $saga->aggregate_uuid,
            'subscription_id' => $payload['subscription_id'] ?? null,
            'user_id' => $payload['user_id'] ?? null,
            'user_name' => $user?->name ?? 'Unknown',
            'user_email' => $user?->email ?? 'Unknown',
            'amount' => $payload['amount'] ?? 0,
            'reason' => $payload['reason'] ?? 'Unknown',
            'error_message' => $payload['error_message'] ?? null,
            'failed_at' => $saga->occurred_at,
            'attempts_made' => $payload['attempts_made'] ?? 0,
            'full_payload' => $payload,
        ]);
    }
}

