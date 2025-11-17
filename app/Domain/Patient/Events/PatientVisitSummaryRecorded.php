<?php

namespace App\Domain\Patient\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a patient's visit summary / longitudinal
 * medical history is recorded.
 *
 * Payload aligns with medical_surgical_histories and family_medical_histories
 * (plus related family_medical_conditions): patient_id, past_injuries,
 * past_injuries_details, surgery, surgery_details,
 * chronic_conditions_details, chronic_pain, chronic_pain_details,
 * family_history_conditions (array of names or ['name' => string] arrays).
 */
class PatientVisitSummaryRecorded extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'patient';
    }

    public static function eventType(): string
    {
        return 'patient.visit_summary_recorded';
    }
}

