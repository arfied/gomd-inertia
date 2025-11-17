<?php

namespace App\Domain\Patient\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a patient's demographics are updated.
 *
 * The payload should use existing User column names to avoid duplication,
 * e.g. fname, lname, gender, dob, address1, address2, city, state, zip,
 * phone, mobile_phone, etc., plus user_id to link back to the User row.
 */
class PatientDemographicsUpdated extends DomainEvent
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
        return 'patient.demographics_updated';
    }
}

