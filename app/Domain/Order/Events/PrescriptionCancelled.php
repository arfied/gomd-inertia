<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

class PrescriptionCancelled extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order.prescription_cancelled';
    }

    public static function aggregateType(): string
    {
        return 'order';
    }
}

