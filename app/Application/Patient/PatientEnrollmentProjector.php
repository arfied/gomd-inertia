<?php

namespace App\Application\Patient;

use App\Domain\Patient\Events\PatientEnrolled;

interface PatientEnrollmentProjector
{
    public function project(PatientEnrolled $event): void;
}

