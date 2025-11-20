<?php

namespace App\Application\Agent\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * ActivateAgentAccount Command
 *
 * Final command in the Agent Onboarding Saga.
 * Activates the agent's account and completes the onboarding process.
 */
class ActivateAgentAccount implements Command
{
    public function __construct(
        public string $agentUuid,
        public string $sagaUuid,
        public int $agentId,
        public array $metadata = [],
    ) {
    }
}

