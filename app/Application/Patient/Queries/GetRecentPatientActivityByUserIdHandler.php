<?php

namespace App\Application\Patient\Queries;

use App\Application\Patient\PatientActivityFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\Activity;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GetRecentPatientActivityByUserIdHandler implements QueryHandler
{
    public function __construct(
        private PatientActivityFinder $finder,
    ) {
    }

    /**
     * @return Collection<int, Activity>
     */
    public function handle(Query $query): Collection
    {
        if (! $query instanceof GetRecentPatientActivityByUserId) {
            throw new InvalidArgumentException('GetRecentPatientActivityByUserIdHandler can only handle GetRecentPatientActivityByUserId queries.');
        }

        return $this->finder->findRecentByUserId($query->userId, $query->limit);
    }
}

