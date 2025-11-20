<?php

namespace App\Domain\Commission\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when an agent requests a payout.
 *
 * This event initiates the payout process for pending commissions.
 */
class PayoutRequested extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'payout';
    }

    public static function eventType(): string
    {
        return 'payout.requested';
    }
}

