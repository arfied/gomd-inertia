<?php

namespace App\Listeners;

use App\Domain\Compliance\Events\AccessLogged;
use App\Models\AuditTrailReadModel;

class ProjectAccessLogged
{
    public function handle(AccessLogged $event): void
    {
        $payload = $event->payload;

        AuditTrailReadModel::create([
            'audit_uuid' => $event->aggregateUuid,
            'patient_id' => $payload['patient_id'] ?? null,
            'accessed_by' => $payload['accessed_by'] ?? null,
            'access_type' => $payload['access_type'] ?? null,
            'resource' => $payload['resource'] ?? null,
            'accessed_at' => $payload['accessed_at'] ?? $event->occurredAt,
            'ip_address' => $payload['ip_address'] ?? null,
            'user_agent' => $payload['user_agent'] ?? null,
            'created_at' => $event->occurredAt,
        ]);
    }
}

