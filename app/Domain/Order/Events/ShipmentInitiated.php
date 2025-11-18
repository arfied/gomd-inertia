<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

class ShipmentInitiated extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order.shipment_initiated';
    }

    public static function aggregateType(): string
    {
        return 'order';
    }
}

