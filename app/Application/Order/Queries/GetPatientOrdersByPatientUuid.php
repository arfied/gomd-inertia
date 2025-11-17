<?php

namespace App\Application\Order\Queries;

use App\Domain\Shared\Queries\Query;

class GetPatientOrdersByPatientUuid implements Query
{
    public function __construct(
        public string $patientUuid,
    ) {
    }
}

