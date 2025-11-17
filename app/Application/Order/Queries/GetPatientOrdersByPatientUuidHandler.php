<?php

namespace App\Application\Order\Queries;

use App\Application\Order\PatientOrderFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\MedicationOrder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GetPatientOrdersByPatientUuidHandler implements QueryHandler
{
    public function __construct(
        private PatientOrderFinder $finder,
    ) {
    }

    /**
     * @return Collection<int, MedicationOrder>
     */
    public function handle(Query $query): Collection
    {
        if (! $query instanceof GetPatientOrdersByPatientUuid) {
            throw new InvalidArgumentException('GetPatientOrdersByPatientUuidHandler can only handle GetPatientOrdersByPatientUuid queries.');
        }

        return $this->finder->findByPatientUuid($query->patientUuid);
    }
}

