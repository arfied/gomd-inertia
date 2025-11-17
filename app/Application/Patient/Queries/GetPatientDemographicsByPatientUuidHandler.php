<?php

namespace App\Application\Patient\Queries;

use App\Application\Patient\PatientDemographicsFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\User;
use InvalidArgumentException;

class GetPatientDemographicsByPatientUuidHandler implements QueryHandler
{
    public function __construct(
        private PatientDemographicsFinder $finder,
    ) {
    }

    public function handle(Query $query): ?User
    {
        if (! $query instanceof GetPatientDemographicsByPatientUuid) {
            throw new InvalidArgumentException('GetPatientDemographicsByPatientUuidHandler can only handle GetPatientDemographicsByPatientUuid queries.');
        }

        return $this->finder->findByPatientUuid($query->patientUuid);
    }
}

