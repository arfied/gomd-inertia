<?php

namespace App\Domain\Clinical\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a questionnaire is created.
 *
 * Payload includes: title, description, questions (array), status, created_by, patient_id.
 */
class QuestionnaireCreated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'questionnaire';
    }

    public static function eventType(): string
    {
        return 'questionnaire.created';
    }
}

