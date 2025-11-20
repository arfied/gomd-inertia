<?php

namespace App\Application\Analytics\EventHandlers;

use App\Domain\Subscription\Events\PaymentFailed;
use App\Models\Subscription;
use App\Models\SubscriptionAnalyticsView;
use App\Models\SubscriptionPlan;

class PaymentFailedHandler
{
    public function handle(PaymentFailed $event): void
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

        // Update the analytics view with payment failure info
        $analyticsView = SubscriptionAnalyticsView::where('subscription_id', $subscriptionId)->first();

        if ($analyticsView) {
            $analyticsView->update([
                'status' => 'pending_payment',
                'churn_reason' => 'payment_failed',
            ]);
        } else {
            // Create if doesn't exist
            SubscriptionAnalyticsView::create([
                'subscription_id' => $subscriptionId,
                'user_id' => $subscription->user_id,
                'plan_id' => $subscription->plan_id,
                'plan_name' => $plan?->name ?? 'Unknown',
                'monthly_price' => $plan?->price ?? 0,
                'status' => 'pending_payment',
                'started_at' => $subscription->starts_at,
                'ended_at' => $subscription->ends_at,
                'cancelled_at' => $subscription->cancelled_at,
                'is_trial' => $subscription->is_trial ?? false,
                'total_revenue' => 0,
                'months_active' => 0,
                'churn_reason' => 'payment_failed',
            ]);
        }
    }
}

