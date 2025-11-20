<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when subscription renewal saga completes successfully.
 *
 * Payload should include:
 * - subscription_id: The renewed subscription
 * - transaction_id: The payment transaction ID
 * - renewed_at: When the renewal occurred
 * - next_billing_date: When the next renewal is due
 */
class SubscriptionRenewalSagaCompleted extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'subscription_renewal_saga';
    }

    public static function eventType(): string
    {
        return 'subscription_renewal_saga.completed';
    }
}

