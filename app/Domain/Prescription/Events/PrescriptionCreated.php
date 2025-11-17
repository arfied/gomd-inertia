<?php

namespace App\Domain\Prescription\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a prescription is created.
 *
 * Payload is aligned with the legacy prescriptions table so that
 * projections can rebuild that table from the event stream.
 */
class PrescriptionCreated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'prescription';
    }

    public static function eventType(): string
    {
        return 'prescription.created';
    }
}

