<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

class InventoryReserved extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order.inventory_reserved';
    }

    public static function aggregateType(): string
    {
        return 'order';
    }
}

