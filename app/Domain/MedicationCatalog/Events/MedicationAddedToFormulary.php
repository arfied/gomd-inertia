<?php

namespace App\Domain\MedicationCatalog\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a medication is added to a formulary.
 */
class MedicationAddedToFormulary extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'formulary';
    }

    public static function eventType(): string
    {
        return 'formulary.medication_added';
    }
}

