<?php

namespace App\Domain\Payment\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a payment method is removed.
 *
 * Payload should include:
 * - reason: Reason for removal (user_requested, expired, invalid, etc.)
 * - removed_at: When the removal occurred
 */
class PaymentMethodRemoved extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'payment_method';
    }

    public static function eventType(): string
    {
        return 'payment_method.removed';
    }
}

