<?php

namespace App\Application\Agent\Jobs;

use App\Domain\Agent\Events\AgentCredentialsVerified;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * VerifyAgentCredentialsJob
 *
 * Part of the Agent Onboarding Saga.
 * Verifies agent credentials and dispatches AgentCredentialsVerified event.
 */
class VerifyAgentCredentialsJob implements ShouldQueue
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
        // Verify credentials (placeholder logic)
        $verified = true;

        if ($verified) {
            event(new AgentCredentialsVerified(
                aggregateUuid: $this->agentUuid,
                payload: $this->payload,
            ));
        }
    }
}

