<?php

namespace App\Application\Patient;

use App\Models\PatientEnrollment;

class EloquentPatientEnrollmentFinder implements PatientEnrollmentFinder
{
    public function findByUserId(int $userId): ?PatientEnrollment
    {
        return PatientEnrollment::where('user_id', $userId)->first();
    }
}

