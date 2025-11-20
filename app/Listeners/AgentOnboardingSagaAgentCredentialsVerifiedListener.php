<?php

namespace App\Listeners;

use App\Application\Agent\Handlers\AgentOnboardingSagaHandler;
use App\Domain\Agent\Events\AgentCredentialsVerified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * AgentOnboardingSagaAgentCredentialsVerifiedListener
 *
 * Listens for AgentCredentialsVerified events and continues the onboarding saga.
 */
class AgentOnboardingSagaAgentCredentialsVerifiedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AgentCredentialsVerified $event): void
    {
        $handler = new AgentOnboardingSagaHandler();
        $handler->handleAgentCredentialsVerified($event);
    }
}

