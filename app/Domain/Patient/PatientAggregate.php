<?php

namespace App\Domain\Patient;

use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Skeleton aggregate for Patient bounded context.
 *
 * This does not yet replace the existing Patient/User models.
 */
class PatientAggregate extends AggregateRoot
{
    public string $uuid;

    protected function apply(DomainEvent $event): void
    {
        // In the future, pattern-match on event types, e.g.:
        // if ($event instanceof PatientEnrolled) { ... }
    }
}

