<?php

namespace App\Application\Patient\Queries;

use App\Domain\Shared\Queries\Query;

class GetPatientEnrollmentByUserId implements Query
{
    public function __construct(
        public int $userId,
    ) {
    }
}

