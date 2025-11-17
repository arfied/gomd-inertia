<?php

namespace App\Application\Patient;

use App\Domain\Patient\Events\PatientDocumentUploaded;
use App\Models\MedicalRecord;

class EloquentPatientDocumentProjector implements PatientDocumentProjector
{
    public function project(PatientDocumentUploaded $event): void
    {
        $patientId = $event->payload['patient_id'] ?? null;
        $recordType = $event->payload['record_type'] ?? null;
        $description = $event->payload['description'] ?? null;
        $recordDate = $event->payload['record_date'] ?? null;

        if ($patientId === null || $recordType === null || $description === null) {
            return;
        }

        MedicalRecord::create([
            'patient_id' => $patientId,
            'doctor_id' => $event->payload['doctor_id'] ?? null,
            'record_type' => $recordType,
            'description' => $description,
            'record_date' => $recordDate ?? $event->occurredAt->format('Y-m-d'),
            'file_path' => $event->payload['file_path'] ?? null,
        ]);
    }
}

