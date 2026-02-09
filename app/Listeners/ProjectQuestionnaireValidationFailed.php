<?php

namespace App\Listeners;

use App\Domain\Questionnaire\Events\QuestionnaireValidationFailed;

class ProjectQuestionnaireValidationFailed
{
    /**
     * Handle the event.
     *
     * Validation failures are tracked in the event store but don't need to be
     * projected to the read model since responses are stored separately.
     * This listener can be used for logging or analytics if needed.
     */
    public function handle(QuestionnaireValidationFailed $event): void
    {
        // Validation failures are recorded in the event store
        // No read model update needed since responses are stored separately
        // This can be extended for logging, metrics, or analytics
    }
}

