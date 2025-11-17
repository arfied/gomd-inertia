<?php

namespace App\Application\Patient\Queries;

use App\Application\Patient\PatientDocumentFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\MedicalRecord;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GetPatientDocumentsByPatientUuidHandler implements QueryHandler
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
        if (! $query instanceof GetPatientDocumentsByPatientUuid) {
            throw new InvalidArgumentException('GetPatientDocumentsByPatientUuidHandler can only handle GetPatientDocumentsByPatientUuid queries.');
        }

        return $this->finder->findByPatientUuid($query->patientUuid);
    }
}

