<?php

namespace App\Domain\Commission\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a referral hierarchy is established.
 *
 * This event records the relationship between agents in the hierarchy
 * and their commission rates based on tier.
 */
class ReferralHierarchyCreated extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'referral_hierarchy';
    }

    public static function eventType(): string
    {
        return 'referral_hierarchy.created';
    }
}

