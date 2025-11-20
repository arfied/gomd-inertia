<?php

namespace App\Application\Agent\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * SendAgentWelcomeKit Command
 *
 * Part of the Agent Onboarding Saga.
 * Sends welcome kit to the agent and triggers mentor assignment.
 */
class SendAgentWelcomeKit implements Command
{
    public function __construct(
        public string $agentUuid,
        public string $sagaUuid,
        public int $agentId,
        public array $metadata = [],
    ) {
    }
}

