<?php

namespace App\Application\Patient;

use App\Domain\Patient\Events\PatientDemographicsUpdated;

interface PatientDemographicsProjector
{
    public function project(PatientDemographicsUpdated $event): void;
}

