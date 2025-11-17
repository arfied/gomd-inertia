<?php

namespace App\Application\Patient\Queries;

use App\Application\Patient\PatientMedicalHistoryFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use InvalidArgumentException;

class GetPatientMedicalHistoryByUserIdHandler implements QueryHandler
{
    public function __construct(
        private PatientMedicalHistoryFinder $finder,
    ) {
    }

    /**
     * @return array{
     *   allergies: array<int, array<string, mixed>>,
     *   conditions: array<int, array<string, mixed>>,
     *   medications: array<int, array<string, mixed>>,
     *   surgical_history: array<string, mixed>|null,
     *   family_history: array<string, mixed>|null
     * }
     */
    public function handle(Query $query): array
    {
        if (! $query instanceof GetPatientMedicalHistoryByUserId) {
            throw new InvalidArgumentException('GetPatientMedicalHistoryByUserIdHandler can only handle GetPatientMedicalHistoryByUserId queries.');
        }

        return $this->finder->findByUserId($query->userId);
    }
}

