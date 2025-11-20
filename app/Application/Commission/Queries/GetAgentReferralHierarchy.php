<?php

namespace App\Application\Commission\Queries;

use App\Domain\Shared\Queries\Query;

class GetAgentReferralHierarchy implements Query
{
    public function __construct(
        public int $agentId,
        public int $depth = 3, // How many levels deep to fetch
    ) {
    }
}

