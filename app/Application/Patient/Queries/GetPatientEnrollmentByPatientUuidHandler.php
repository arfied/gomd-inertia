<?php

namespace App\Application\Patient\Queries;

use App\Application\Patient\PatientEnrollmentFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\PatientEnrollment;
use InvalidArgumentException;

class GetPatientEnrollmentByPatientUuidHandler implements QueryHandler
{
    public function __construct(
        private PatientEnrollmentFinder $finder,
    ) {
    }

    public function handle(Query $query): ?PatientEnrollment
    {
        if (! $query instanceof GetPatientEnrollmentByPatientUuid) {
            throw new InvalidArgumentException('GetPatientEnrollmentByPatientUuidHandler can only handle GetPatientEnrollmentByPatientUuid queries.');
        }

        return $this->finder->findByPatientUuid($query->patientUuid);
    }
}

