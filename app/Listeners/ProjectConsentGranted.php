<?php

namespace App\Listeners;

use App\Domain\Compliance\Events\ConsentGranted;
use App\Models\ConsentReadModel;

class ProjectConsentGranted
{
    public function handle(ConsentGranted $event): void
    {
        $payload = $event->payload;

        ConsentReadModel::updateOrCreate(
            ['consent_uuid' => $event->aggregateUuid],
            [
                'patient_id' => $payload['patient_id'] ?? null,
                'consent_type' => $payload['consent_type'] ?? null,
                'granted_by' => $payload['granted_by'] ?? null,
                'granted_at' => $payload['granted_at'] ?? $event->occurredAt,
                'expires_at' => $payload['expires_at'] ?? null,
                'terms_version' => $payload['terms_version'] ?? null,
                'status' => $payload['status'] ?? 'active',
                'created_at' => $event->occurredAt,
            ],
        );
    }
}

