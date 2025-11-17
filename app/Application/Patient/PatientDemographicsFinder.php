<?php

namespace App\Application\Patient;

use App\Models\User;

interface PatientDemographicsFinder
{
    public function findByUserId(int $userId): ?User;

    public function findByPatientUuid(string $patientUuid): ?User;
}

