<?php

namespace App\Listeners;

use App\Application\Agent\Handlers\AgentOnboardingSagaHandler;
use App\Domain\Agent\Events\AgentReferralLinksCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * AgentOnboardingSagaAgentReferralLinksCreatedListener
 *
 * Listens for AgentReferralLinksCreated events and continues the onboarding saga.
 */
class AgentOnboardingSagaAgentReferralLinksCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AgentReferralLinksCreated $event): void
    {
        $handler = new AgentOnboardingSagaHandler();
        $handler->handleAgentReferralLinksCreated($event);
    }
}

