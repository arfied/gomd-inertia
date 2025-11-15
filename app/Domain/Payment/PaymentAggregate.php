<?php

namespace App\Domain\Payment;

use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Skeleton aggregate for Payment bounded context.
 */
class PaymentAggregate extends AggregateRoot
{
    public string $uuid;

    protected function apply(DomainEvent $event): void
    {
        // To be implemented when payment-related events are defined.
    }
}

