<?php

namespace App\Domain\MedicationCatalog\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a condition is created in the catalog.
 */
class ConditionCreated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'condition';
    }

    public static function eventType(): string
    {
        return 'condition.created';
    }
}

