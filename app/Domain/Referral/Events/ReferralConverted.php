<?php

namespace App\Domain\Referral\Events;

use App\Domain\Events\DomainEvent;

/**
 * ReferralConverted
 *
 * Fired when a referral is converted (patient/business enrolled).
 */
class ReferralConverted extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'referral_link';
    }

    public static function eventType(): string
    {
        return 'referral_link.converted';
    }
}

