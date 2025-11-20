<?php

namespace App\Domain\Commission\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a commission is cancelled.
 *
 * This can occur when the associated subscription or transaction is cancelled.
 */
class CommissionCancelled extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'commission';
    }

    public static function eventType(): string
    {
        return 'commission.cancelled';
    }
}

