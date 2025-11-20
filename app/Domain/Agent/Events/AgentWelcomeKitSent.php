<?php

namespace App\Domain\Agent\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when welcome kit is sent to an agent.
 *
 * Part of the Agent Onboarding Saga.
 * Triggers mentor assignment.
 */
class AgentWelcomeKitSent extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'agent';
    }

    public static function eventType(): string
    {
        return 'agent.welcome_kit_sent';
    }
}

