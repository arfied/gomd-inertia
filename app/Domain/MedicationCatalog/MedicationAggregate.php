<?php

namespace App\Domain\MedicationCatalog;

use App\Domain\Events\DomainEvent;
use App\Domain\MedicationCatalog\Events\MedicationCreated;
use App\Domain\MedicationCatalog\Events\MedicationUpdated;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for the Medication bounded context.
 */
class MedicationAggregate extends AggregateRoot
{
    public string $uuid;

    /**
     * Create a new medication aggregate and record a MedicationCreated event.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function create(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new MedicationCreated($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Update an existing medication and record a MedicationUpdated event.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function update(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new MedicationUpdated($uuid, $payload, $metadata));

        return $aggregate;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof MedicationCreated || $event instanceof MedicationUpdated) {
            $this->uuid = $event->aggregateUuid;
        }
    }
}

