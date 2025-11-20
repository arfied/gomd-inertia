<?php

namespace App\Domain\Payment\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a payment method is set as default.
 *
 * Payload should include:
 * - previous_default_id: The ID of the previous default payment method (if any)
 * - set_as_default_at: When this was set as default
 */
class PaymentMethodSetAsDefault extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'payment_method';
    }

    public static function eventType(): string
    {
        return 'payment_method.set_as_default';
    }
}

