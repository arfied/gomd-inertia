<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when an order is assigned to a doctor.
 */
class OrderAssignedToDoctor extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'order';
    }

    public static function eventType(): string
    {
        return 'order.assigned_to_doctor';
    }
}

