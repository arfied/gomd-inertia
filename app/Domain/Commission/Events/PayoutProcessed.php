<?php

namespace App\Domain\Commission\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a payout is processed for an agent.
 *
 * This event marks the completion of a payout transaction,
 * including all commissions included in the payout.
 */
class PayoutProcessed extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'payout';
    }

    public static function eventType(): string
    {
        return 'payout.processed';
    }
}

