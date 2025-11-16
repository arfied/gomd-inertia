<?php

namespace App\Application\Patient;

use App\Domain\Patient\Events\PatientEnrolled;
use App\Models\Activity;

class EloquentPatientActivityProjector implements PatientActivityProjector
{
    public function projectPatientEnrolled(PatientEnrolled $event): void
    {
        $userId = $event->payload['user_id'] ?? null;

        if ($userId === null) {
            return;
        }

        Activity::create([
            'user_id' => $userId,
            'logged_by' => $userId,
            'type' => $event::eventType(),
            'description' => 'Patient enrolled in TeleMed Pro.',
            'metadata' => [
                'patient_uuid' => $event->aggregateUuid,
                'source' => $event->metadata['source'] ?? null,
            ],
        ]);
    }
}

