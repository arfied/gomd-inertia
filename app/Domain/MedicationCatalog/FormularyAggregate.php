<?php

namespace App\Domain\MedicationCatalog;

use App\Domain\Events\DomainEvent;
use App\Domain\MedicationCatalog\Events\FormularyCreated;
use App\Domain\MedicationCatalog\Events\FormularyUpdated;
use App\Domain\MedicationCatalog\Events\MedicationAddedToFormulary;
use App\Domain\MedicationCatalog\Events\MedicationRemovedFromFormulary;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for the Formulary bounded context.
 *
 * A formulary is a curated list of approved medications for a specific
 * organization, insurance plan, or clinical protocol.
 */
class FormularyAggregate extends AggregateRoot
{
    public string $uuid;

    /**
     * Create a new formulary aggregate and record a FormularyCreated event.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function create(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new FormularyCreated($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Update an existing formulary and record a FormularyUpdated event.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function update(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new FormularyUpdated($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Add a medication to the formulary.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function addMedication(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new MedicationAddedToFormulary($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Remove a medication from the formulary.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function removeMedication(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new MedicationRemovedFromFormulary($uuid, $payload, $metadata));

        return $aggregate;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof FormularyCreated
            || $event instanceof FormularyUpdated
            || $event instanceof MedicationAddedToFormulary
            || $event instanceof MedicationRemovedFromFormulary
        ) {
            $this->uuid = $event->aggregateUuid;
        }
    }
}

