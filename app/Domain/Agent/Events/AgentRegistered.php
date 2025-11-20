<?php

namespace App\Domain\Agent\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when an agent is registered in the system.
 *
 * This is the first event in the Agent Onboarding Saga.
 * Triggers credential verification and license checking.
 */
class AgentRegistered extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'agent';
    }

    public static function eventType(): string
    {
        return 'agent.registered';
    }
}

