<?php

namespace App\Listeners;

use App\Application\Patient\PatientMedicalHistoryProjector;
use App\Domain\Patient\Events\PatientMedicationAdded;

class ProjectPatientMedicalHistoryMedication
{
    public function __construct(
        private PatientMedicalHistoryProjector $projector,
    ) {
    }

    public function handle(PatientMedicationAdded $event): void
    {
        $this->projector->projectMedicationAdded($event);
    }
}

