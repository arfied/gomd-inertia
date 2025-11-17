<?php

namespace App\Listeners;

use App\Application\Patient\PatientDemographicsProjector;
use App\Domain\Patient\Events\PatientDemographicsUpdated;

class ProjectPatientDemographics
{
    public function __construct(
        private PatientDemographicsProjector $projector,
    ) {
    }

    public function handle(PatientDemographicsUpdated $event): void
    {
        $this->projector->project($event);
    }
}

