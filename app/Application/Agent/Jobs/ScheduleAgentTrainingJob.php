<?php

namespace App\Application\Agent\Jobs;

use App\Domain\Agent\Events\AgentTrainingScheduled;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * ScheduleAgentTrainingJob
 *
 * Part of the Agent Onboarding Saga.
 * Schedules training for the agent and dispatches AgentTrainingScheduled event.
 */
class ScheduleAgentTrainingJob implements ShouldQueue
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
        // Schedule training (placeholder logic)
        // In production, this would create a calendar event or send scheduling request

        event(new AgentTrainingScheduled(
            aggregateUuid: $this->agentUuid,
            payload: $this->payload,
        ));
    }
}

