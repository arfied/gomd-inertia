<?php

namespace App\Application\Patient\Queries;

use App\Domain\Shared\Queries\Query;

class GetRecentPatientActivityByUserId implements Query
{
    public function __construct(
        public int $userId,
        public int $limit = 5,
    ) {
    }
}

