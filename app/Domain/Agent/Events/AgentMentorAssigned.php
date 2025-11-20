<?php

namespace App\Domain\Agent\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a mentor is assigned to an agent.
 *
 * Part of the Agent Onboarding Saga.
 * Triggers training scheduling.
 */
class AgentMentorAssigned extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'agent';
    }

    public static function eventType(): string
    {
        return 'agent.mentor_assigned';
    }
}

