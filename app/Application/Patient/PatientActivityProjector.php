<?php

namespace App\Application\Patient;

use App\Domain\Patient\Events\PatientEnrolled;

interface PatientActivityProjector
{
    public function projectPatientEnrolled(PatientEnrolled $event): void;
}

