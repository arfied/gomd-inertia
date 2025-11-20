<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a subscription is created.
 *
 * Payload is aligned with the legacy subscriptions table so that
 * projections can rebuild that table from the event stream.
 */
class SubscriptionCreated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'subscription';
    }

    public static function eventType(): string
    {
        return 'subscription.created';
    }
}

