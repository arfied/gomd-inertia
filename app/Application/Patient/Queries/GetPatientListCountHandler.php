<?php

namespace App\Application\Patient\Queries;

use App\Application\Patient\PatientListFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use InvalidArgumentException;

class GetPatientListCountHandler implements QueryHandler
{
    public function __construct(
        private PatientListFinder $finder,
    ) {
    }

    public function handle(Query $query): int
    {
        if (! $query instanceof GetPatientListCount) {
            throw new InvalidArgumentException('GetPatientListCountHandler can only handle GetPatientListCount queries.');
        }

        return $this->finder->count($query->search, $query->filters);
    }
}

