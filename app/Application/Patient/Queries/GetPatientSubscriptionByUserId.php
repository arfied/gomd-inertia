<?php

namespace App\Application\Patient\Queries;

use App\Domain\Shared\Queries\Query;

class GetPatientSubscriptionByUserId implements Query
{
    public function __construct(
        public int $userId,
    ) {
    }
}

