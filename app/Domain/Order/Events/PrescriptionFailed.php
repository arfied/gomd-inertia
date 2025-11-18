<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

class PrescriptionFailed extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order.prescription_failed';
    }

    public static function aggregateType(): string
    {
        return 'order';
    }
}

