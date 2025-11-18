<?php

namespace App\Application\Order\Queries;

use App\Domain\Shared\Queries\Query;

class GetPatientOrderTimelineByPatientUuid implements Query
{
    public function __construct(
        public string $patientUuid,
        public int $limit = 50,
        public ?string $filter = null,
    ) {
    }
}

