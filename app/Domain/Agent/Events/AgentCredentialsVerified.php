<?php

namespace App\Domain\Agent\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when an agent's credentials are verified.
 *
 * Part of the Agent Onboarding Saga.
 * Triggers license checking.
 */
class AgentCredentialsVerified extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'agent';
    }

    public static function eventType(): string
    {
        return 'agent.credentials_verified';
    }
}

