<?php

namespace App\Application\Patient\Queries;

use App\Domain\Shared\Queries\Query;

class GetPatientList implements Query
{
    public function __construct(
        public ?string $search = null,
        public int $perPage = 15,
    ) {
    }
}

