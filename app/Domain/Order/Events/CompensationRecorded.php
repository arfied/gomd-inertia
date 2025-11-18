<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

class CompensationRecorded extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order_fulfillment_saga.compensation_recorded';
    }

    public static function aggregateType(): string
    {
        return 'order_fulfillment_saga';
    }
}

