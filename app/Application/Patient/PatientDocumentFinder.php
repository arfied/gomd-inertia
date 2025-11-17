<?php

namespace App\Application\Patient;

use App\Models\MedicalRecord;
use Illuminate\Support\Collection;

interface PatientDocumentFinder
{
    /**
     * @return Collection<int, MedicalRecord>
     */
    public function findByUserId(int $userId): Collection;

    /**
     * @return Collection<int, MedicalRecord>
     */
    public function findByPatientUuid(string $patientUuid): Collection;
}

