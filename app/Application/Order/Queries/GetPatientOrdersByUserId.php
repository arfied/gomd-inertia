<?php

namespace App\Application\Order\Queries;

use App\Domain\Shared\Queries\Query;

class GetPatientOrdersByUserId implements Query
{
    public function __construct(
        public int $userId,
    ) {
    }
}

