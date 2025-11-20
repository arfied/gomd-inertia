<?php

namespace App\Application\Agent\Jobs;

use App\Domain\Agent\Events\AgentLicenseChecked;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * CheckAgentLicenseJob
 *
 * Part of the Agent Onboarding Saga.
 * Checks agent license and dispatches AgentLicenseChecked event.
 */
class CheckAgentLicenseJob implements ShouldQueue
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
        // Check license (placeholder logic)
        $valid = true;

        if ($valid) {
            event(new AgentLicenseChecked(
                aggregateUuid: $this->agentUuid,
                payload: $this->payload,
            ));
        }
    }
}

