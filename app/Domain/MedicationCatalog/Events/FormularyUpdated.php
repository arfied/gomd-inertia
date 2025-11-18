<?php

namespace App\Domain\MedicationCatalog\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a formulary is updated.
 */
class FormularyUpdated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'formulary';
    }

    public static function eventType(): string
    {
        return 'formulary.updated';
    }
}

