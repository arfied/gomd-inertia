<?php

namespace App\Application\Patient\Queries;

use App\Domain\Shared\Queries\Query;

class GetPatientMedicalHistoryByUserId implements Query
{
    public function __construct(
        public int $userId,
    ) {
    }
}

