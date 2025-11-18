<?php

namespace App\Domain\MedicationCatalog\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a formulary is created.
 *
 * A formulary is a curated list of approved medications for a specific
 * organization, insurance plan, or clinical protocol.
 */
class FormularyCreated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'formulary';
    }

    public static function eventType(): string
    {
        return 'formulary.created';
    }
}

