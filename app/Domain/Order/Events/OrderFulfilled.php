<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when an order is fulfilled.
 *
 * Typically corresponds to medication being shipped/completed for the patient.
 */
class OrderFulfilled extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'order';
    }

    public static function eventType(): string
    {
        return 'order.fulfilled';
    }
}

