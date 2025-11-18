<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

class OrderFulfillmentSagaStateChanged extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order_fulfillment_saga.state_changed';
    }

    public static function aggregateType(): string
    {
        return 'order_fulfillment_saga';
    }
}

