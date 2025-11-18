<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

class OrderFulfillmentSagaStarted extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order_fulfillment_saga.started';
    }

    public static function aggregateType(): string
    {
        return 'order_fulfillment_saga';
    }
}

