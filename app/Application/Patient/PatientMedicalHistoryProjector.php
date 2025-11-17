<?php

namespace App\Application\Patient;

use App\Domain\Patient\Events\PatientAllergyRecorded;
use App\Domain\Patient\Events\PatientConditionRecorded;
use App\Domain\Patient\Events\PatientMedicationAdded;
use App\Domain\Patient\Events\PatientVisitSummaryRecorded;

interface PatientMedicalHistoryProjector
{
    public function projectAllergyRecorded(PatientAllergyRecorded $event): void;

    public function projectConditionRecorded(PatientConditionRecorded $event): void;

    public function projectMedicationAdded(PatientMedicationAdded $event): void;

    public function projectVisitSummaryRecorded(PatientVisitSummaryRecorded $event): void;
}

