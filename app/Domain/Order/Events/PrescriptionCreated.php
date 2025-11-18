<?php

namespace App\Domain\Order\Events;

use App\Domain\Events\DomainEvent;

class PrescriptionCreated extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order.prescription_created';
    }

    public static function aggregateType(): string
    {
        return 'order';
    }
}

