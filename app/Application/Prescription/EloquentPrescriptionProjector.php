<?php

namespace App\Application\Prescription;

use App\Domain\Prescription\Events\PrescriptionCreated;
use App\Models\Prescription;

class EloquentPrescriptionProjector implements PrescriptionProjector
{
    public function projectPrescriptionCreated(PrescriptionCreated $event): void
    {
        $payload = $event->payload;

        $patientId = $payload['user_id'] ?? null;

        if ($patientId === null) {
            return;
        }

        Prescription::create([
            'user_id' => $patientId,
            'doctor_id' => $payload['doctor_id'] ?? null,
            'pharmacist_id' => $payload['pharmacist_id'] ?? null,
            'status' => $payload['status'] ?? 'pending',
            'notes' => $payload['notes'] ?? null,
            'is_non_standard' => $payload['is_non_standard'] ?? false,
        ]);
    }
}

