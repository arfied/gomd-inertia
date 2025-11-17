<?php

namespace App\Listeners;

use App\Application\Patient\PatientMedicalHistoryProjector;
use App\Domain\Patient\Events\PatientAllergyRecorded;

class ProjectPatientMedicalHistoryAllergy
{
    public function __construct(
        private PatientMedicalHistoryProjector $projector,
    ) {
    }

    public function handle(PatientAllergyRecorded $event): void
    {
        $this->projector->projectAllergyRecorded($event);
    }
}

