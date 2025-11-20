<?php

namespace App\Application\Agent\Jobs;

use App\Domain\Agent\Events\AgentAccountActivated;
use App\Models\Agent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * ActivateAgentAccountJob
 *
 * Final job in the Agent Onboarding Saga.
 * Activates the agent's account and dispatches AgentAccountActivated event.
 */
class ActivateAgentAccountJob implements ShouldQueue
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
        $agentId = $this->payload['agent_id'] ?? null;
        if ($agentId) {
            $agent = Agent::find($agentId);
            if ($agent) {
                $agent->update(['status' => 'active']);
            }
        }

        event(new AgentAccountActivated(
            aggregateUuid: $this->agentUuid,
            payload: $this->payload,
        ));
    }
}

