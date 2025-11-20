<?php

namespace App\Domain\Shared;

use App\Domain\Events\DomainEvent;

/**
 * Base class for event-sourced aggregates.
 *
 * This is intentionally minimal scaffolding and does not yet
 * integrate with any specific domain models.
 */
abstract class AggregateRoot
{
    /** @var array<int, DomainEvent> */
    protected array $recordedEvents = [];

    /**
     * Record a new domain event and apply it to the aggregate.
     */
    protected function recordThat(DomainEvent $event): void
    {
        $this->recordedEvents[] = $event;
        $this->apply($event);
    }

    /**
     * Reconstitute aggregate state from a history of events.
     *
     * @param iterable<DomainEvent> $events
     */
    public static function reconstituteFromHistory(iterable $events): static
    {
        $instance = new static();

        foreach ($events as $event) {
            $instance->apply($event);
        }

        return $instance;
    }

    /**
     * Get recorded events without clearing them.
     *
     * @return array<int, DomainEvent>
     */
    public function getRecordedEvents(): array
    {
        return $this->recordedEvents;
    }

    /**
     * Release and clear recorded events.
     *
     * @return array<int, DomainEvent>
     */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    /**
     * Apply a domain event to the aggregate's state.
     */
    abstract protected function apply(DomainEvent $event): void;
}

