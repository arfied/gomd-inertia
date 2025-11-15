<?php

namespace App\Listeners;

use App\Application\Patient\PatientEnrollmentProjector;
use App\Domain\Patient\Events\PatientEnrolled;

class ProjectPatientEnrollment
{
    public function __construct(
        private PatientEnrollmentProjector $projector,
    ) {
    }

    public function handle(PatientEnrolled $event): void
    {
        $this->projector->project($event);
    }
}

