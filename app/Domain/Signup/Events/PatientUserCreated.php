<?php

namespace App\Domain\Signup\Events;

use App\Domain\Events\DomainEvent;

/**
 * Event fired when a patient user is created during signup flow.
 *
 * This event is dispatched when the user provides their email address
 * during the signup flow, allowing the user to be created before
 * questionnaire submission.
 */
class PatientUserCreated extends DomainEvent
{
    public function __construct(
        public string $signupId,
        public string $email,
        public string $userId,
        public array $metadata = [],
    ) {
        parent::__construct(
            aggregateUuid: $signupId,
            payload: [
                'email' => $email,
                'user_id' => $userId,
            ],
            metadata: $metadata,
        );
    }

    public static function aggregateType(): string
    {
        return 'signup';
    }

    public static function eventType(): string
    {
        return 'signup.patient_user_created';
    }
}

