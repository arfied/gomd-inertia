<?php

namespace App\Domain\Payment\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a payment method is updated.
 *
 * Payload should include:
 * - updated_fields: Array of fields that were updated
 * - previous_values: Array of previous values for auditing
 * - new_values: Array of new values
 */
class PaymentMethodUpdated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'payment_method';
    }

    public static function eventType(): string
    {
        return 'payment_method.updated';
    }
}

