<?php

namespace App\Listeners;

use App\Application\Agent\Handlers\AgentOnboardingSagaHandler;
use App\Domain\Agent\Events\AgentWelcomeKitSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * AgentOnboardingSagaAgentWelcomeKitSentListener
 *
 * Listens for AgentWelcomeKitSent events and continues the onboarding saga.
 */
class AgentOnboardingSagaAgentWelcomeKitSentListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AgentWelcomeKitSent $event): void
    {
        $handler = new AgentOnboardingSagaHandler();
        $handler->handleAgentWelcomeKitSent($event);
    }
}

