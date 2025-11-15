<?php

namespace App\Application\Patient\Queries;

use App\Application\Patient\PatientEnrollmentFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\PatientEnrollment;
use InvalidArgumentException;

class GetPatientEnrollmentByUserIdHandler implements QueryHandler
{
    public function __construct(
        private PatientEnrollmentFinder $finder,
    ) {
    }

    public function handle(Query $query): ?PatientEnrollment
    {
        if (! $query instanceof GetPatientEnrollmentByUserId) {
            throw new InvalidArgumentException('GetPatientEnrollmentByUserIdHandler can only handle GetPatientEnrollmentByUserId queries.');
        }

        return $this->finder->findByUserId($query->userId);
    }
}

