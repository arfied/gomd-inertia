<?php

namespace App\Application\Patient\Queries;

use App\Application\Patient\PatientListFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use Illuminate\Contracts\Pagination\Paginator;
use InvalidArgumentException;

class GetPatientListHandler implements QueryHandler
{
    public function __construct(
        private PatientListFinder $finder,
    ) {
    }

    public function handle(Query $query): Paginator
    {
        if (! $query instanceof GetPatientList) {
            throw new InvalidArgumentException('GetPatientListHandler can only handle GetPatientList queries.');
        }

        return $this->finder->paginate($query->search, $query->perPage, $query->filters);
    }
}

