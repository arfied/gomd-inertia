<?php

namespace App\Application\Analytics\EventHandlers;

use App\Domain\Subscription\Events\SubscriptionCancelled;
use App\Models\Subscription;
use App\Models\SubscriptionAnalyticsView;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;

class SubscriptionCancelledHandler
{
    public function handle(SubscriptionCancelled $event): void
    {
        $payload = $event->payload;
        $subscriptionId = $payload['subscription_id'] ?? null;

        if (!$subscriptionId) {
            return;
        }

        // Get the subscription from the database
        $subscription = Subscription::find($subscriptionId);
        if (!$subscription) {
            return;
        }

        // Get the plan details
        $plan = SubscriptionPlan::find($subscription->plan_id);

        // Calculate months active
        $monthsActive = $this->calculateMonthsActive($subscription->starts_at, $subscription->cancelled_at ?? now());

        // Calculate total revenue
        $monthlyPrice = $plan?->price ?? 0;
        $totalRevenue = $monthlyPrice * $monthsActive;

        // Get churn reason from payload
        $churnReason = $payload['cancellation_reason'] ?? 'unknown';

        // Update the analytics view
        SubscriptionAnalyticsView::updateOrCreate(
            ['subscription_id' => $subscriptionId],
            [
                'user_id' => $subscription->user_id,
                'plan_id' => $subscription->plan_id,
                'plan_name' => $plan?->name ?? 'Unknown',
                'monthly_price' => $monthlyPrice,
                'status' => 'cancelled',
                'started_at' => $subscription->starts_at,
                'ended_at' => $subscription->ends_at,
                'cancelled_at' => $subscription->cancelled_at ?? now(),
                'is_trial' => $subscription->is_trial ?? false,
                'total_revenue' => $totalRevenue,
                'months_active' => $monthsActive,
                'churn_reason' => $churnReason,
            ]
        );
    }

    private function calculateMonthsActive(?Carbon $startDate, ?Carbon $endDate): int
    {
        if (!$startDate || !$endDate) {
            return 0;
        }

        return $startDate->diffInMonths($endDate);
    }
}

