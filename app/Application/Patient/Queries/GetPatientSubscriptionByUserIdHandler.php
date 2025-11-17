<?php

namespace App\Application\Patient\Queries;

use App\Application\Patient\PatientSubscriptionFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\Subscription;
use InvalidArgumentException;

class GetPatientSubscriptionByUserIdHandler implements QueryHandler
{
    public function __construct(
        private PatientSubscriptionFinder $finder,
    ) {
    }

    public function handle(Query $query): ?Subscription
    {
        if (! $query instanceof GetPatientSubscriptionByUserId) {
            throw new InvalidArgumentException('GetPatientSubscriptionByUserIdHandler can only handle GetPatientSubscriptionByUserId queries.');
        }

        return $this->finder->findCurrentByUserId($query->userId);
    }
}

