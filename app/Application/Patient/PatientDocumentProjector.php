<?php

namespace App\Application\Patient;

use App\Domain\Patient\Events\PatientDocumentUploaded;

interface PatientDocumentProjector
{
    public function project(PatientDocumentUploaded $event): void;
}

