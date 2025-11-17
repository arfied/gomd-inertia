<?php

namespace App\Listeners;

use App\Application\Prescription\PrescriptionProjector;
use App\Domain\Prescription\Events\PrescriptionCreated;

class ProjectPrescriptionCreated
{
    public function __construct(
        private PrescriptionProjector $projector,
    ) {
    }

    public function handle(PrescriptionCreated $event): void
    {
        $this->projector->projectPrescriptionCreated($event);
    }
}

