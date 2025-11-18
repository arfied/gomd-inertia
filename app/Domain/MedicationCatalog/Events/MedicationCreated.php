<?php

namespace App\Domain\MedicationCatalog\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a medication is created in the catalog.
 *
 * Payload is aligned with the legacy medications table so that
 * projections can rebuild that table from the event stream.
 */
class MedicationCreated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'medication';
    }

    public static function eventType(): string
    {
        return 'medication.created';
    }
}

