<?php

namespace App\Application\Commission\Queries;

use App\Domain\Shared\Queries\Query;

class GetRecentCommissions implements Query
{
    public function __construct(
        public int $agentId,
        public int $limit = 10,
        public int $page = 1,
    ) {
    }
}

