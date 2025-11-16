<?php

namespace App\Application\Patient\Queries;

use App\Application\Patient\PatientTimelineFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GetPatientEventTimelineByUserIdHandler implements QueryHandler
{
    public function __construct(
        private PatientTimelineFinder $finder,
    ) {
    }

    public function handle(Query $query): Collection
    {
        if (! $query instanceof GetPatientEventTimelineByUserId) {
            throw new InvalidArgumentException('GetPatientEventTimelineByUserIdHandler can only handle GetPatientEventTimelineByUserId queries.');
        }

        return $this->finder->findTimelineByUserId($query->userId, $query->limit);
    }
}

