<?php

namespace App\Application\Agent\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * AssignAgentMentor Command
 *
 * Part of the Agent Onboarding Saga.
 * Assigns a mentor to the agent and triggers training scheduling.
 */
class AssignAgentMentor implements Command
{
    public function __construct(
        public string $agentUuid,
        public string $sagaUuid,
        public int $agentId,
        public ?int $mentorId = null,
        public array $metadata = [],
    ) {
    }
}

