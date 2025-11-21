<?php

namespace App\Listeners;

use App\Domain\Subscription\Events\SubscriptionRenewalSagaStarted;
use App\Jobs\Subscription\ProcessSubscriptionRenewalJob;

/**
 * SubscriptionRenewalSagaStartedListener
 *
 * Listens to SubscriptionRenewalSagaStarted events and dispatches a job
 * to process the payment attempt for subscription renewal.
 */
class SubscriptionRenewalSagaStartedListener
{

    public function handle(SubscriptionRenewalSagaStarted $event): void
    {
        $sagaUuid = $event->aggregateUuid;
        $subscriptionId = $event->payload['subscription_id'] ?? null;
        $userId = $event->payload['user_id'] ?? null;
        $amount = $event->payload['amount'] ?? 0;
        $correlationId = $event->payload['correlation_id'] ?? null;

        if (!$subscriptionId || !$userId) {
            return;
        }

        \Illuminate\Support\Facades\Log::info('SubscriptionRenewalSagaStartedListener: Dispatching job', [
            'saga_uuid' => $sagaUuid,
            'subscription_id' => $subscriptionId,
            'user_id' => $userId,
            'amount' => $amount,
            'correlation_id' => $correlationId,
        ]);

        dispatch(new ProcessSubscriptionRenewalJob(
            $sagaUuid,
            $subscriptionId,
            $userId,
            $amount,
            $correlationId,
        ))->onQueue('subscription-renewal');
    }
}

