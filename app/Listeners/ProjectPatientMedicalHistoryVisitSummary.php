<?php

namespace App\Listeners;

use App\Application\Patient\PatientMedicalHistoryProjector;
use App\Domain\Patient\Events\PatientVisitSummaryRecorded;

class ProjectPatientMedicalHistoryVisitSummary
{
    public function __construct(
        private PatientMedicalHistoryProjector $projector,
    ) {
    }

    public function handle(PatientVisitSummaryRecorded $event): void
    {
        $this->projector->projectVisitSummaryRecorded($event);
    }
}

