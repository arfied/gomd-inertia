<?php

namespace App\Domain\Prescription;

use App\Domain\Events\DomainEvent;
use App\Domain\Prescription\Events\PrescriptionCreated;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for the Prescription bounded context.
 */
class PrescriptionAggregate extends AggregateRoot
{
    public string $uuid;

    /**
     * Create a new prescription aggregate and record a PrescriptionCreated event.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function create(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new PrescriptionCreated($uuid, $payload, $metadata));

        return $aggregate;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof PrescriptionCreated) {
            $this->uuid = $event->aggregateUuid;
        }
    }
}

