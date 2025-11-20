<?php

namespace App\Domain\Commission\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a commission is earned by an agent/LOA.
 *
 * This event is triggered when an order is fulfilled and commissions
 * cascade through the referral hierarchy.
 */
class CommissionEarned extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'commission';
    }

    public static function eventType(): string
    {
        return 'commission.earned';
    }
}

