<?php

namespace App\Domain\Clinical\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a consultation is scheduled.
 *
 * Payload includes: consultation_id, patient_id, doctor_id, scheduled_at, duration, notes.
 */
class ConsultationScheduled extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'consultation';
    }

    public static function eventType(): string
    {
        return 'consultation.scheduled';
    }
}

