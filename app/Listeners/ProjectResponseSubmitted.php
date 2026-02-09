<?php

namespace App\Listeners;

use App\Domain\Clinical\Events\ResponseSubmitted;
use App\Models\QuestionnaireResponse;
use Illuminate\Support\Str;

class ProjectResponseSubmitted
{
    /**
     * Handle the event.
     *
     * Creates a new questionnaire response record instead of updating the read model.
     * This allows multiple responses per questionnaire to be stored and tracked.
     */
    public function handle(ResponseSubmitted $event): void
    {
        $payload = $event->payload;
        $questionnaireId = $payload['questionnaire_id'] ?? null;
        $patientId = $payload['patient_id'] ?? null;

        if (! $questionnaireId || ! $patientId) {
            return;
        }

        QuestionnaireResponse::create([
            'response_uuid' => (string) Str::uuid(),
            'questionnaire_uuid' => $questionnaireId,
            'patient_id' => $patientId,
            'responses' => $payload['responses'] ?? [],
            'metadata' => $payload['metadata'] ?? [],
            'submitted_at' => $payload['submitted_at'] ?? now(),
        ]);
    }
}

