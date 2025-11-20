<?php

namespace App\Domain\Agent\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when referral links are created for an agent.
 *
 * Part of the Agent Onboarding Saga.
 * Triggers welcome kit sending.
 */
class AgentReferralLinksCreated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'agent';
    }

    public static function eventType(): string
    {
        return 'agent.referral_links_created';
    }
}

