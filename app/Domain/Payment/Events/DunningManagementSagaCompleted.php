<?php

namespace App\Domain\Payment\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when dunning management saga completes successfully.
 *
 * Payload should include:
 * - subscription_id: The subscription that was recovered
 * - user_id: The user who paid
 * - transaction_id: The successful transaction ID
 * - amount: The amount collected
 * - attempt_number: Which attempt succeeded
 * - completed_at: When the saga completed
 */
class DunningManagementSagaCompleted extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'dunning_management_saga';
    }

    public static function eventType(): string
    {
        return 'dunning_management_saga.completed';
    }
}

