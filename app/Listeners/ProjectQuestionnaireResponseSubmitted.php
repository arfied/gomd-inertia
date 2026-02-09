<?php

namespace App\Listeners;

use App\Domain\Questionnaire\Events\QuestionnaireResponseSubmitted;
use App\Models\QuestionnaireResponse;
use Illuminate\Support\Str;

class ProjectQuestionnaireResponseSubmitted
{
    /**
     * Handle the event.
     *
     * Creates a new questionnaire response record instead of updating the read model.
     * This allows multiple responses per questionnaire to be stored and tracked.
     */
    public function handle(QuestionnaireResponseSubmitted $event): void
    {
        QuestionnaireResponse::create([
            'response_uuid' => (string) Str::uuid(),
            'questionnaire_uuid' => $event->aggregateUuid,
            'patient_id' => $event->patientId,
            'responses' => $event->responses,
            'metadata' => $event->metadata ?? [],
            'submitted_at' => $event->occurredAt,
        ]);
    }
}

