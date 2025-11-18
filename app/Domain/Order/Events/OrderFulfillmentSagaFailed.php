<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

class OrderFulfillmentSagaFailed extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order_fulfillment_saga.failed';
    }

    public static function aggregateType(): string
    {
        return 'order_fulfillment_saga';
    }
}

