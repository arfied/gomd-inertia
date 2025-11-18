<?php

namespace App\Domain\MedicationCatalog\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a condition is updated in the catalog.
 */
class ConditionUpdated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'condition';
    }

    public static function eventType(): string
    {
        return 'condition.updated';
    }
}

