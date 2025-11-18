<?php

namespace App\Application\Order\Queries;

use App\Domain\Shared\Queries\Query;

class GetPatientOrderTimelineByUserId implements Query
{
    public function __construct(
        public int $userId,
        public int $limit = 50,
        public ?string $filter = null,
    ) {
    }
}

