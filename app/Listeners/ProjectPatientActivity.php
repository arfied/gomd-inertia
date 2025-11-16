<?php

namespace App\Listeners;

use App\Application\Patient\PatientActivityProjector;
use App\Domain\Patient\Events\PatientEnrolled;

class ProjectPatientActivity
{
    public function __construct(
        private PatientActivityProjector $projector,
    ) {
    }

    public function handle(PatientEnrolled $event): void
    {
        $this->projector->projectPatientEnrolled($event);
    }
}

