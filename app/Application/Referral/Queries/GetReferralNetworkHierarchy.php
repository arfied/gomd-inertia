<?php

namespace App\Application\Referral\Queries;

use App\Domain\Shared\Queries\Query;

/**
 * GetReferralNetworkHierarchy Query
 *
 * Retrieves the complete referral network hierarchy for an agent.
 */
class GetReferralNetworkHierarchy implements Query
{
    public function __construct(
        public int $agentId,
        public ?int $depth = null,
    ) {
    }
}

