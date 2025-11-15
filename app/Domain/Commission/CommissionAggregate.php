<?php

namespace App\Domain\Commission;

use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Skeleton aggregate for Commission bounded context.
 */
class CommissionAggregate extends AggregateRoot
{
    public string $uuid;

    protected function apply(DomainEvent $event): void
    {
        // To be implemented when commission-related events are defined.
    }
}

