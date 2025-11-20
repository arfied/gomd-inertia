<?php

namespace App\Application\Agent\Jobs;

use App\Application\Agent\Handlers\CreateAgentReferralLinksHandler;
use App\Domain\Agent\Events\AgentReferralLinksCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * CreateAgentReferralLinksJob
 *
 * Part of the Agent Onboarding Saga.
 * Creates referral links for the agent and dispatches AgentReferralLinksCreated event.
 */
class CreateAgentReferralLinksJob implements ShouldQueue
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
            (new CreateAgentReferralLinksHandler())->handle($agentId);
        }

        event(new AgentReferralLinksCreated(
            aggregateUuid: $this->agentUuid,
            payload: $this->payload,
        ));
    }
}

