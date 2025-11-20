<?php

namespace App\Application\Agent\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * ScheduleAgentTraining Command
 *
 * Part of the Agent Onboarding Saga.
 * Schedules training for the agent and triggers account activation.
 */
class ScheduleAgentTraining implements Command
{
    public function __construct(
        public string $agentUuid,
        public string $sagaUuid,
        public int $agentId,
        public ?string $trainingDate = null,
        public array $metadata = [],
    ) {
    }
}

