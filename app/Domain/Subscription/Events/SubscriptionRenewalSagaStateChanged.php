<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when subscription renewal saga state changes.
 *
 * Payload should include:
 * - from_state: Previous state
 * - to_state: New state
 * - event_type: What triggered the state change
 */
class SubscriptionRenewalSagaStateChanged extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'subscription_renewal_saga';
    }

    public static function eventType(): string
    {
        return 'subscription_renewal_saga.state_changed';
    }
}

