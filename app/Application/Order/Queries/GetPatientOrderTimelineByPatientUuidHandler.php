<?php

namespace App\Application\Order\Queries;

use App\Application\Order\StaffPatientOrderTimelineFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GetPatientOrderTimelineByPatientUuidHandler implements QueryHandler
{
    public function __construct(
        private StaffPatientOrderTimelineFinder $finder,
    ) {
    }

    public function handle(Query $query): Collection
    {
        if (! $query instanceof GetPatientOrderTimelineByPatientUuid) {
            throw new InvalidArgumentException('GetPatientOrderTimelineByPatientUuidHandler can only handle GetPatientOrderTimelineByPatientUuid queries.');
        }

        return $this->finder->findTimelineByPatientUuid($query->patientUuid, $query->limit, $query->filter);
    }
}

