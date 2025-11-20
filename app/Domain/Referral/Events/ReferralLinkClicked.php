<?php

namespace App\Domain\Referral\Events;

use App\Domain\Events\DomainEvent;

/**
 * ReferralLinkClicked
 *
 * Fired when a referral link is clicked/tracked.
 */
class ReferralLinkClicked extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'referral_link';
    }

    public static function eventType(): string
    {
        return 'referral_link.clicked';
    }
}

