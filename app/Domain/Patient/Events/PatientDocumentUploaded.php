<?php

namespace App\Domain\Patient\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a document is uploaded to a patient's record.
 *
 * The payload is expected to align with the existing medical_records table:
 * patient_id, doctor_id, record_type, description, record_date, file_path.
 */
class PatientDocumentUploaded extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'patient';
    }

    public static function eventType(): string
    {
        return 'patient.document_uploaded';
    }
}

