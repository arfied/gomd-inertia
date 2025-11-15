<?php

namespace App\Domain\Order;

use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Skeleton aggregate for Order bounded context.
 */
class OrderAggregate extends AggregateRoot
{
    public string $uuid;

    protected function apply(DomainEvent $event): void
    {
        // To be implemented when order-related events are defined.
    }
}

