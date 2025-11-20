<?php

namespace App\Application\Referral\Queries;

use App\Domain\Shared\Queries\Query;

/**
 * GetAgentReferralPerformance Query
 *
 * Retrieves referral performance metrics for an agent.
 */
class GetAgentReferralPerformance implements Query
{
    public function __construct(
        public int $agentId,
        public ?string $period = 'month',
    ) {
    }
}

