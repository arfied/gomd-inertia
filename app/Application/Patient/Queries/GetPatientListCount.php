<?php

namespace App\Application\Patient\Queries;

use App\Domain\Shared\Queries\Query;

class GetPatientListCount implements Query
{
    public function __construct(
        public ?string $search = null,
    ) {
    }
}

