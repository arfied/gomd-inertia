<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

class InventoryReleased extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order.inventory_released';
    }

    public static function aggregateType(): string
    {
        return 'order';
    }
}

