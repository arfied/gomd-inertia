<?php

namespace App\Application\Patient;

use App\Models\MedicalRecord;
use App\Models\PatientEnrollment;
use Illuminate\Support\Collection;

class EloquentPatientDocumentFinder implements PatientDocumentFinder
{
    public function findByUserId(int $userId): Collection
    {
        return MedicalRecord::query()
            ->where('patient_id', $userId)
            ->orderByDesc('record_date')
            ->orderByDesc('id')
            ->get();
    }

    public function findByPatientUuid(string $patientUuid): Collection
    {
        $enrollment = PatientEnrollment::where('patient_uuid', $patientUuid)->first();

        if ($enrollment === null) {
            return collect();
        }

        return $this->findByUserId($enrollment->user_id);
    }
}

