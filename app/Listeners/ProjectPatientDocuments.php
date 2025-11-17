<?php

namespace App\Listeners;

use App\Application\Patient\PatientDocumentProjector;
use App\Domain\Patient\Events\PatientDocumentUploaded;

class ProjectPatientDocuments
{
    public function __construct(
        private PatientDocumentProjector $projector,
    ) {
    }

    public function handle(PatientDocumentUploaded $event): void
    {
        $this->projector->project($event);
    }
}

