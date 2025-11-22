<?php

namespace App\Listeners;

use App\Domain\Clinical\Events\QuestionnaireCreated;
use App\Models\QuestionnaireReadModel;

class ProjectQuestionnaireCreated
{
    public function handle(QuestionnaireCreated $event): void
    {
        $payload = $event->payload;

        QuestionnaireReadModel::updateOrCreate(
            ['questionnaire_uuid' => $event->aggregateUuid],
            [
                'title' => $payload['title'] ?? null,
                'description' => $payload['description'] ?? null,
                'questions' => json_encode($payload['questions'] ?? []),
                'created_by' => $payload['created_by'] ?? null,
                'patient_id' => $payload['patient_id'] ?? null,
                'status' => 'active',
                'created_at' => $event->occurredAt,
            ],
        );
    }
}

