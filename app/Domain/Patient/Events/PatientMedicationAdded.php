<?php

namespace App\Domain\Patient\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a patient's medication history entry is added.
 *
 * Payload aligns with the medication_histories table: user_id, medication_id,
 * start_date, end_date, dosage, frequency, notes.
 */
class PatientMedicationAdded extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'patient';
    }

    public static function eventType(): string
    {
        return 'patient.medication_added';
    }
}

