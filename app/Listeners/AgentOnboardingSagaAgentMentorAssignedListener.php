<?php

namespace App\Listeners;

use App\Application\Agent\Handlers\AgentOnboardingSagaHandler;
use App\Domain\Agent\Events\AgentMentorAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * AgentOnboardingSagaAgentMentorAssignedListener
 *
 * Listens for AgentMentorAssigned events and continues the onboarding saga.
 */
class AgentOnboardingSagaAgentMentorAssignedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AgentMentorAssigned $event): void
    {
        $handler = new AgentOnboardingSagaHandler();
        $handler->handleAgentMentorAssigned($event);
    }
}

