<?php

namespace App\Application\Patient\Queries;

use App\Application\Patient\PatientDemographicsFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\User;
use InvalidArgumentException;

class GetPatientDemographicsByUserIdHandler implements QueryHandler
{
    public function __construct(
        private PatientDemographicsFinder $finder,
    ) {
    }

    public function handle(Query $query): ?User
    {
        if (! $query instanceof GetPatientDemographicsByUserId) {
            throw new InvalidArgumentException('GetPatientDemographicsByUserIdHandler can only handle GetPatientDemographicsByUserId queries.');
        }

        return $this->finder->findByUserId($query->userId);
    }
}

