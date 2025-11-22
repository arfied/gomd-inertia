<?php

namespace App\Domain\Clinical\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a patient submits responses to a questionnaire.
 *
 * Payload includes: questionnaire_id, patient_id, responses (array), submitted_at.
 */
class ResponseSubmitted extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'questionnaire';
    }

    public static function eventType(): string
    {
        return 'questionnaire.response_submitted';
    }
}

