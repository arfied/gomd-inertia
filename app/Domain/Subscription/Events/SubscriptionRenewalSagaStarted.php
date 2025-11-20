<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a subscription renewal saga starts.
 *
 * Payload should include:
 * - subscription_id: The subscription being renewed
 * - user_id: The user who owns the subscription
 * - plan_id: The plan being renewed
 * - amount: The renewal amount
 * - billing_date: When the renewal is due
 */
class SubscriptionRenewalSagaStarted extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'subscription_renewal_saga';
    }

    public static function eventType(): string
    {
        return 'subscription_renewal_saga.started';
    }
}

