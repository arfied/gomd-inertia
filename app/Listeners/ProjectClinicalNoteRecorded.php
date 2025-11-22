<?php

namespace App\Listeners;

use App\Domain\Clinical\Events\ClinicalNoteRecorded;
use App\Models\ClinicalNoteReadModel;

class ProjectClinicalNoteRecorded
{
    public function handle(ClinicalNoteRecorded $event): void
    {
        $payload = $event->payload;

        ClinicalNoteReadModel::updateOrCreate(
            ['clinical_note_uuid' => $event->aggregateUuid],
            [
                'patient_id' => $payload['patient_id'] ?? null,
                'doctor_id' => $payload['doctor_id'] ?? null,
                'note_type' => $payload['note_type'] ?? null,
                'content' => $payload['content'] ?? null,
                'attachments' => json_encode($payload['attachments'] ?? []),
                'recorded_at' => $payload['recorded_at'] ?? $event->occurredAt,
                'created_at' => $event->occurredAt,
            ],
        );
    }
}

