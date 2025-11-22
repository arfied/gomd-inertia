<?php

namespace App\Listeners;

use App\Domain\Clinical\Events\ConsultationScheduled;
use App\Models\ConsultationReadModel;

class ProjectConsultationScheduled
{
    public function handle(ConsultationScheduled $event): void
    {
        $payload = $event->payload;

        ConsultationReadModel::updateOrCreate(
            ['consultation_uuid' => $event->aggregateUuid],
            [
                'patient_id' => $payload['patient_id'] ?? null,
                'doctor_id' => $payload['doctor_id'] ?? null,
                'scheduled_at' => $payload['scheduled_at'] ?? null,
                'reason' => $payload['reason'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'status' => $payload['status'] ?? 'scheduled',
                'created_at' => $event->occurredAt,
            ],
        );
    }
}

