<?php

namespace App\Domain\Payment\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a dunning management saga starts.
 *
 * Payload should include:
 * - subscription_id: The subscription with failed payment
 * - user_id: The user being dunned
 * - amount: The amount owed
 * - max_attempts: Maximum retry attempts (default: 5)
 * - retry_schedule: Days between retries [1, 3, 7, 14, 30]
 * - started_at: When the saga started
 */
class DunningManagementSagaStarted extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'dunning_management_saga';
    }

    public static function eventType(): string
    {
        return 'dunning_management_saga.started';
    }
}

