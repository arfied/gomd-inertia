<?php

namespace App\Domain\Agent\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when training is scheduled for an agent.
 *
 * Part of the Agent Onboarding Saga.
 * Triggers account activation.
 */
class AgentTrainingScheduled extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'agent';
    }

    public static function eventType(): string
    {
        return 'agent.training_scheduled';
    }
}

