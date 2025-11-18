<?php

namespace App\Domain\MedicationCatalog;

use App\Domain\Events\DomainEvent;
use App\Domain\MedicationCatalog\Events\ConditionCreated;
use App\Domain\MedicationCatalog\Events\ConditionUpdated;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for the Condition bounded context.
 */
class ConditionAggregate extends AggregateRoot
{
    public string $uuid;

    /**
     * Create a new condition aggregate and record a ConditionCreated event.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function create(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new ConditionCreated($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Update an existing condition and record a ConditionUpdated event.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function update(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new ConditionUpdated($uuid, $payload, $metadata));

        return $aggregate;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof ConditionCreated || $event instanceof ConditionUpdated) {
            $this->uuid = $event->aggregateUuid;
        }
    }
}

