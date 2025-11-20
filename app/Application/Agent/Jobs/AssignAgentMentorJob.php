<?php

namespace App\Application\Agent\Jobs;

use App\Domain\Agent\Events\AgentMentorAssigned;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * AssignAgentMentorJob
 *
 * Part of the Agent Onboarding Saga.
 * Assigns a mentor to the agent and dispatches AgentMentorAssigned event.
 */
class AssignAgentMentorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $agentUuid,
        public array $payload,
    ) {
        $this->onQueue('agent-onboarding');
    }

    public function handle(): void
    {
        // Assign mentor (placeholder logic)
        // In production, this would find an appropriate mentor based on tier/region

        event(new AgentMentorAssigned(
            aggregateUuid: $this->agentUuid,
            payload: $this->payload,
        ));
    }
}

