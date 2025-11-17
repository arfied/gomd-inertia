<?php

namespace App\Application\Order;

use App\Models\MedicationOrder;
use App\Models\PatientEnrollment;
use Illuminate\Support\Collection;

class EloquentPatientOrderFinder implements PatientOrderFinder
{
    public function findByUserId(int $userId): Collection
    {
        return MedicationOrder::query()
            ->with(['items.medication'])
            ->where('patient_id', $userId)
            ->orderByDesc('created_at')
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

