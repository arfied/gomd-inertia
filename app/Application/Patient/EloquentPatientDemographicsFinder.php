<?php

namespace App\Application\Patient;

use App\Models\PatientEnrollment;
use App\Models\User;

class EloquentPatientDemographicsFinder implements PatientDemographicsFinder
{
    public function findByUserId(int $userId): ?User
    {
        return User::find($userId);
    }

    public function findByPatientUuid(string $patientUuid): ?User
    {
        $enrollment = PatientEnrollment::where('patient_uuid', $patientUuid)->first();

        if ($enrollment === null) {
            return null;
        }

        return User::find($enrollment->user_id);
    }
}

