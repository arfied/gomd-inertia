<?php

namespace App\Domain\Patient\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a patient's medical condition is recorded.
 *
 * Payload aligns with the medical_conditions table: patient_id,
 * condition_name, diagnosed_at, notes, had_condition_before, is_chronic.
 */
class PatientConditionRecorded extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'patient';
    }

    public static function eventType(): string
    {
        return 'patient.condition_recorded';
    }
}

