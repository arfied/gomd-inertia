<?php

namespace App\Domain\Patient\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a patient is enrolled in the system.
 *
 * This is the first concrete example built on top of the
 * event-sourcing foundation and TeleMed Pro specification.
 */
class PatientEnrolled extends DomainEvent
{
    /**
     * Logical aggregate type for this event.
     */
    public static function aggregateType(): string
    {
        return 'patient';
    }

    /**
     * Logical event type identifier used for routing/analytics.
     */
    public static function eventType(): string
    {
        return 'patient.enrolled';
    }
}

