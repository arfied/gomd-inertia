<?php

namespace App\Application\Patient\Queries;

use App\Domain\Shared\Queries\Query;

class GetPatientEnrollmentByPatientUuid implements Query
{
    public function __construct(
        public string $patientUuid,
    ) {
    }
}

