<?php

namespace App\Domain\Agent\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when an agent's account is activated.
 *
 * Final event in the Agent Onboarding Saga.
 * Marks the completion of the onboarding process.
 */
class AgentAccountActivated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'agent';
    }

    public static function eventType(): string
    {
        return 'agent.account_activated';
    }
}

