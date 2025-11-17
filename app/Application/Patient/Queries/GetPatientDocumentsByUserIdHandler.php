<?php

namespace App\Application\Patient\Queries;

use App\Application\Patient\PatientDocumentFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\MedicalRecord;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GetPatientDocumentsByUserIdHandler implements QueryHandler
{
    public function __construct(
        private PatientDocumentFinder $finder,
    ) {
    }

    /**
     * @return Collection<int, MedicalRecord>
     */
    public function handle(Query $query): Collection
    {
        if (! $query instanceof GetPatientDocumentsByUserId) {
            throw new InvalidArgumentException('GetPatientDocumentsByUserIdHandler can only handle GetPatientDocumentsByUserId queries.');
        }

        return $this->finder->findByUserId($query->userId);
    }
}

