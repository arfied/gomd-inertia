<?php

namespace App\Listeners;

use App\Application\Agent\Handlers\AgentOnboardingSagaHandler;
use App\Domain\Agent\Events\AgentRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * AgentOnboardingSagaAgentRegisteredListener
 *
 * Listens for AgentRegistered events and triggers the onboarding saga.
 */
class AgentOnboardingSagaAgentRegisteredListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AgentRegistered $event): void
    {
        $handler = new AgentOnboardingSagaHandler();
        $handler->handleAgentRegistered($event);
    }
}

