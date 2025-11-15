<?php

namespace App\Domain\Events;

use App\Models\StoredEvent;

abstract class DomainEvent
{
    /**
     * The aggregate UUID this event belongs to.
     */
    public string $aggregateUuid;

    /**
     * The event payload as a plain array.
     */
    public array $payload;

    /**
     * Arbitrary metadata for tracing/debugging.
     */
    public array $metadata;

    /**
     * When the event occurred.
     */
    public \DateTimeImmutable $occurredAt;

    public function __construct(string $aggregateUuid, array $payload = [], array $metadata = [])
    {
        $this->aggregateUuid = $aggregateUuid;
        $this->payload = $payload;
        $this->metadata = $metadata;
        $this->occurredAt = new \DateTimeImmutable('now');
    }

    /**
     * Logical aggregate type for this event (e.g. Patient, Order).
     */
    public static function aggregateType(): string
    {
        return static::class;
    }

    /**
     * Event type identifier; by default, the FQCN.
     */
    public static function eventType(): string
    {
        return static::class;
    }

    /**
     * Convert to attributes suitable for persistence in the event_store table.
     */
    public function toStoredEventAttributes(): array
    {
        return [
            'aggregate_uuid' => $this->aggregateUuid,
            'aggregate_type' => static::aggregateType(),
            'event_type' => static::eventType(),
            'event_data' => $this->payload,
            'metadata' => $this->metadata,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s.u'),
        ];
    }

    /**
     * Persist this event into the event store.
     */
    public function store(): StoredEvent
    {
        return StoredEvent::create($this->toStoredEventAttributes());
    }
}

