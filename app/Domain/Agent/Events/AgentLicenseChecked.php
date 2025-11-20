<?php

namespace App\Domain\Agent\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when an agent's license is checked.
 *
 * Part of the Agent Onboarding Saga.
 * Triggers referral link creation.
 */
class AgentLicenseChecked extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'agent';
    }

    public static function eventType(): string
    {
        return 'agent.license_checked';
    }
}

