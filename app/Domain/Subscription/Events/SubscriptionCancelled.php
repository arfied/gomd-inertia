<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a subscription is cancelled.
 *
 * Payload should include:
 * - cancelled_at: When the cancellation occurred
 * - cancellation_reason: Reason for cancellation (e.g., 'user_requested', 'payment_failed', 'admin_action')
 * - effective_date: When the cancellation becomes effective (may be immediate or end-of-period)
 */
class SubscriptionCancelled extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'subscription';
    }

    public static function eventType(): string
    {
        return 'subscription.cancelled';
    }
}

