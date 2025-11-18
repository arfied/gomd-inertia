<?php

namespace App\Domain\MedicationCatalog\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a medication is updated in the catalog.
 */
class MedicationUpdated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'medication';
    }

    public static function eventType(): string
    {
        return 'medication.updated';
    }
}

