<?php

namespace App\Listeners;

use App\Application\Agent\Handlers\AgentOnboardingSagaHandler;
use App\Domain\Agent\Events\AgentTrainingScheduled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * AgentOnboardingSagaAgentTrainingScheduledListener
 *
 * Listens for AgentTrainingScheduled events and continues the onboarding saga.
 */
class AgentOnboardingSagaAgentTrainingScheduledListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AgentTrainingScheduled $event): void
    {
        $handler = new AgentOnboardingSagaHandler();
        $handler->handleAgentTrainingScheduled($event);
    }
}

