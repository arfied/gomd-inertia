<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a medication order is created.
 *
 * Payload is aligned with the legacy medication_orders table so that
 * projections can rebuild that table from the event stream.
 */
class OrderCreated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'order';
    }

    public static function eventType(): string
    {
        return 'order.created';
    }
}

