<?php

namespace App\Domain\Patient\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a patient's allergy is recorded.
 *
 * Payload aligns with the existing allergies table: user_id, allergen,
 * reaction, severity, notes.
 */
class PatientAllergyRecorded extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'patient';
    }

    public static function eventType(): string
    {
        return 'patient.allergy_recorded';
    }
}

