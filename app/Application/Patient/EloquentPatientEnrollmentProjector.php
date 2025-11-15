<?php

namespace App\Application\Patient;

use App\Domain\Patient\Events\PatientEnrolled;
use App\Models\PatientEnrollment;

class EloquentPatientEnrollmentProjector implements PatientEnrollmentProjector
{
    public function project(PatientEnrolled $event): void
    {
        PatientEnrollment::updateOrCreate(
            ['patient_uuid' => $event->aggregateUuid],
            [
                'user_id' => $event->payload['user_id'] ?? null,
                'source' => $event->metadata['source'] ?? null,
                'metadata' => $event->metadata,
                'enrolled_at' => $event->occurredAt,
            ],
        );
    }
}

