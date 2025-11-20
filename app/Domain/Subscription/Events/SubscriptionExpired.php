<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a subscription expires.
 *
 * This event is triggered when a subscription reaches its end date
 * and is not renewed (e.g., user did not renew, payment failed).
 *
 * Payload should include:
 * - expired_at: When the expiration occurred
 * - reason: Reason for expiration (e.g., 'end_of_period', 'payment_failed', 'not_renewed')
 */
class SubscriptionExpired extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'subscription';
    }

    public static function eventType(): string
    {
        return 'subscription.expired';
    }
}

