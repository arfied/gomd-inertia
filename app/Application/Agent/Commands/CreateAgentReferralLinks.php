<?php

namespace App\Application\Agent\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * CreateAgentReferralLinks Command
 *
 * Part of the Agent Onboarding Saga.
 * Creates referral links for the agent and triggers welcome kit sending.
 */
class CreateAgentReferralLinks implements Command
{
    public function __construct(
        public string $agentUuid,
        public string $sagaUuid,
        public int $agentId,
        public array $metadata = [],
    ) {
    }
}

