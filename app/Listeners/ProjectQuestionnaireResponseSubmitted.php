<?php

namespace App\Listeners;

use App\Domain\Questionnaire\Events\QuestionnaireResponseSubmitted;
use App\Models\QuestionnaireReadModel;

class ProjectQuestionnaireResponseSubmitted
{
    /**
     * Handle the event.
     */
    public function handle(QuestionnaireResponseSubmitted $event): void
    {
        $questionnaire = QuestionnaireReadModel::where('questionnaire_uuid', $event->aggregateUuid)->first();

        if ($questionnaire) {
            // Update questionnaire with response data
            $questionnaire->update([
                'responses' => $event->responses,
                'patient_id' => $event->patientId,
                'status' => 'submitted',
                'submitted_at' => $event->occurredAt,
            ]);
        }
    }
}

