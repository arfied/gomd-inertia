<?php

namespace App\Application\Patient;

use App\Models\PatientEnrollment;

interface PatientEnrollmentFinder
{
    public function findByUserId(int $userId): ?PatientEnrollment;

    public function findByPatientUuid(string $patientUuid): ?PatientEnrollment;
}

