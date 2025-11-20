<?php

namespace App\Domain\Payment\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when dunning management is initiated for a failed payment.
 *
 * Payload should include:
 * - subscription_id: The subscription with failed payment
 * - user_id: The user being dunned
 * - payment_method_id: The failed payment method
 * - amount: The amount owed
 * - reason: Why dunning was initiated
 * - initiated_at: When dunning started
 */
class DunningInitiated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'dunning_management_saga';
    }

    public static function eventType(): string
    {
        return 'dunning_management_saga.initiated';
    }
}

