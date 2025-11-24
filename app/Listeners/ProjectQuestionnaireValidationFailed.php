<?php

namespace App\Listeners;

use App\Domain\Questionnaire\Events\QuestionnaireValidationFailed;
use App\Models\QuestionnaireReadModel;

class ProjectQuestionnaireValidationFailed
{
    /**
     * Handle the event.
     */
    public function handle(QuestionnaireValidationFailed $event): void
    {
        $questionnaire = QuestionnaireReadModel::where('questionnaire_uuid', $event->aggregateUuid)->first();

        if ($questionnaire) {
            // Update questionnaire with validation failure data
            $questionnaire->update([
                'status' => 'validation_failed',
                'responses' => array_merge(
                    $questionnaire->responses ?? [],
                    ['validation_errors' => $event->errors]
                ),
            ]);
        }
    }
}

