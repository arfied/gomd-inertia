<?php

namespace App\Application\Commission\Queries;

use App\Domain\Shared\Queries\Query;

class GetAgentEarningsOverview implements Query
{
    public function __construct(
        public int $agentId,
        public ?string $period = 'month', // 'day', 'week', 'month', 'year'
    ) {
    }
}

