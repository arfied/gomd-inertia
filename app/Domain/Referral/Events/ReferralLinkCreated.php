<?php

namespace App\Domain\Referral\Events;

use App\Domain\Events\DomainEvent;

/**
 * ReferralLinkCreated
 *
 * Fired when a referral link is created for an agent.
 */
class ReferralLinkCreated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'referral_link';
    }

    public static function eventType(): string
    {
        return 'referral_link.created';
    }
}

