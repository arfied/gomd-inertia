<?php

namespace App\Domain\Payment\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when dunning management saga fails (all retries exhausted).
 *
 * Payload should include:
 * - subscription_id: The subscription that could not be recovered
 * - user_id: The user who failed to pay
 * - amount: The amount owed
 * - attempts_made: Number of attempts made
 * - reason: Why the saga failed
 * - failed_at: When the saga failed
 */
class DunningManagementSagaFailed extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'dunning_management_saga';
    }

    public static function eventType(): string
    {
        return 'dunning_management_saga.failed';
    }
}

