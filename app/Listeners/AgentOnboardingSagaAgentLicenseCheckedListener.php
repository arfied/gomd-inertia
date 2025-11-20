<?php

namespace App\Listeners;

use App\Application\Agent\Handlers\AgentOnboardingSagaHandler;
use App\Domain\Agent\Events\AgentLicenseChecked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * AgentOnboardingSagaAgentLicenseCheckedListener
 *
 * Listens for AgentLicenseChecked events and continues the onboarding saga.
 */
class AgentOnboardingSagaAgentLicenseCheckedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AgentLicenseChecked $event): void
    {
        $handler = new AgentOnboardingSagaHandler();
        $handler->handleAgentLicenseChecked($event);
    }
}

