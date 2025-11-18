<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

class InventoryReservationFailed extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order.inventory_reservation_failed';
    }

    public static function aggregateType(): string
    {
        return 'order';
    }
}

