<?php

namespace App\Application\Agent\Jobs;

use App\Domain\Agent\Events\AgentWelcomeKitSent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * SendAgentWelcomeKitJob
 *
 * Part of the Agent Onboarding Saga.
 * Sends welcome kit to the agent and dispatches AgentWelcomeKitSent event.
 */
class SendAgentWelcomeKitJob implements ShouldQueue
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
        // Send welcome kit (placeholder logic)
        // In production, this would send an email or physical kit

        event(new AgentWelcomeKitSent(
            aggregateUuid: $this->agentUuid,
            payload: $this->payload,
        ));
    }
}

