<?php

namespace App\Listeners;

use App\Domain\Signup\Events\QuestionnaireCompleted;
use App\Models\SignupReadModel;

class ProjectQuestionnaireCompleted
{
    public function handle(QuestionnaireCompleted $event): void
    {
        $signup = SignupReadModel::where('signup_uuid', $event->aggregateUuid)->first();

        if ($signup) {
            $signup->update([
                'questionnaire_responses' => $event->responses,
                'updated_at' => $event->occurredAt,
            ]);
        }
    }
}

