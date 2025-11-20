<?php

namespace App\Domain\Payment\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when dunning management saga state changes.
 *
 * Payload should include:
 * - from_state: Previous state
 * - to_state: New state
 * - event_type: What triggered the state change
 */
class DunningManagementSagaStateChanged extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'dunning_management_saga';
    }

    public static function eventType(): string
    {
        return 'dunning_management_saga.state_changed';
    }
}

