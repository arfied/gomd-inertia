<?php

namespace App\Domain\Clinical\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a clinical note is recorded.
 *
 * Payload includes: note_id, patient_id, doctor_id, content, note_type, recorded_at.
 */
class ClinicalNoteRecorded extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'clinical_note';
    }

    public static function eventType(): string
    {
        return 'clinical_note.recorded';
    }
}

