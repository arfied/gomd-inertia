<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a subscription is renewed.
 *
 * This event is triggered when a subscription's billing cycle completes
 * and a new cycle begins (e.g., monthly renewal, annual renewal).
 *
 * Payload should include:
 * - previous_ends_at: The original end date
 * - new_ends_at: The new end date after renewal
 * - renewal_reason: 'automatic' or 'manual'
 * - transaction_id: Associated payment transaction ID (if applicable)
 */
class SubscriptionRenewed extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'subscription';
    }

    public static function eventType(): string
    {
        return 'subscription.renewed';
    }
}

