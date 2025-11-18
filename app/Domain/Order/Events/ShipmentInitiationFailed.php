<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

class ShipmentInitiationFailed extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order.shipment_initiation_failed';
    }

    public static function aggregateType(): string
    {
        return 'order';
    }
}

