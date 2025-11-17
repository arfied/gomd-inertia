<?php

namespace App\Application\Prescription;

use App\Domain\Prescription\Events\PrescriptionCreated;

interface PrescriptionProjector
{
    public function projectPrescriptionCreated(PrescriptionCreated $event): void;
}

