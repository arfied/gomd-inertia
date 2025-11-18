<?php

namespace App\Application\Order\Queries;

use App\Application\Order\PatientOrderTimelineFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GetPatientOrderTimelineByUserIdHandler implements QueryHandler
{
    public function __construct(
        private PatientOrderTimelineFinder $finder,
    ) {
    }

    public function handle(Query $query): Collection
    {
        if (! $query instanceof GetPatientOrderTimelineByUserId) {
            throw new InvalidArgumentException('GetPatientOrderTimelineByUserIdHandler can only handle GetPatientOrderTimelineByUserId queries.');
        }

        return $this->finder->findTimelineByUserId($query->userId, $query->limit, $query->filter);
    }
}

