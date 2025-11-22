<?php

namespace App\Domain\Compliance\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a patient grants consent.
 *
 * Payload includes: patient_id, consent_type, granted_by, granted_at, expires_at, terms_version.
 */
class ConsentGranted extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'consent';
    }

    public static function eventType(): string
    {
        return 'consent.granted';
    }
}

