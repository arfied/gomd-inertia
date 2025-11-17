<?php

namespace App\Listeners;

use App\Application\Patient\PatientMedicalHistoryProjector;
use App\Domain\Patient\Events\PatientConditionRecorded;

class ProjectPatientMedicalHistoryCondition
{
    public function __construct(
        private PatientMedicalHistoryProjector $projector,
    ) {
    }

    public function handle(PatientConditionRecorded $event): void
    {
        $this->projector->projectConditionRecorded($event);
    }
}

