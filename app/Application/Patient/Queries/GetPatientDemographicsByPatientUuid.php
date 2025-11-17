<?php

namespace App\Application\Patient\Queries;

use App\Domain\Shared\Queries\Query;

class GetPatientDemographicsByPatientUuid implements Query
{
    public function __construct(
        public string $patientUuid,
    ) {
    }
}

