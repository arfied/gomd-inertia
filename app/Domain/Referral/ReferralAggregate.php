<?php

namespace App\Domain\Referral;

use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Skeleton aggregate for Referral bounded context.
 */
class ReferralAggregate extends AggregateRoot
{
    public string $uuid;

    protected function apply(DomainEvent $event): void
    {
        // To be implemented when referral-related events are defined.
    }
}

