<?php

namespace App\Listeners;

use App\Domain\Clinical\Events\ResponseSubmitted;
use App\Models\QuestionnaireReadModel;

class ProjectResponseSubmitted
{
    public function handle(ResponseSubmitted $event): void
    {
        $payload = $event->payload;
        $questionnaireId = $payload['questionnaire_id'] ?? null;

        if (! $questionnaireId) {
            return;
        }

        $questionnaire = QuestionnaireReadModel::where('questionnaire_uuid', $questionnaireId)->first();

        if ($questionnaire) {
            $questionnaire->update([
                'responses' => json_encode($payload['responses'] ?? []),
                'submitted_at' => $payload['submitted_at'] ?? now(),
                'status' => 'completed',
            ]);
        }
    }
}

