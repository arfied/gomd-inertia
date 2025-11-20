<?php

namespace App\Application\Analytics\EventHandlers;

use App\Domain\Subscription\Events\PaymentAttempted;
use App\Models\Subscription;
use App\Models\SubscriptionAnalyticsView;
use App\Models\SubscriptionPlan;

class PaymentAttemptedHandler
{
    public function handle(PaymentAttempted $event): void
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

        // Update the analytics view with payment attempt info
        $analyticsView = SubscriptionAnalyticsView::where('subscription_id', $subscriptionId)->first();

        if ($analyticsView) {
            $analyticsView->update([
                'last_payment_date' => now(),
                'next_payment_date' => $subscription->ends_at,
            ]);
        } else {
            // Create if doesn't exist
            SubscriptionAnalyticsView::create([
                'subscription_id' => $subscriptionId,
                'user_id' => $subscription->user_id,
                'plan_id' => $subscription->plan_id,
                'plan_name' => $plan?->name ?? 'Unknown',
                'monthly_price' => $plan?->price ?? 0,
                'status' => $subscription->status,
                'started_at' => $subscription->starts_at,
                'ended_at' => $subscription->ends_at,
                'cancelled_at' => $subscription->cancelled_at,
                'is_trial' => $subscription->is_trial ?? false,
                'total_revenue' => 0,
                'months_active' => 0,
                'last_payment_date' => now(),
                'next_payment_date' => $subscription->ends_at,
            ]);
        }
    }
}

