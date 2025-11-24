<?php

namespace App\Domain\Signup\Events;

use App\Domain\Events\DomainEvent;

class MedicationSelected extends DomainEvent
{
    public function __construct(
        public readonly string $signupId,
        public readonly string $medicationName,
        array $payload = [],
        array $metadata = [],
    ) {
        parent::__construct($signupId, $payload, $metadata);
    }

    /**
     * Reconstruct event from stored event data.
     * Used during event rehydration from the event store.
     */
    public static function fromStoredEventData(string $aggregateUuid, array $eventData, array $metadata = []): self
    {
        return new self(
            signupId: $eventData['signup_id'] ?? $aggregateUuid,
            medicationName: $eventData['medication_name'] ?? '',
            payload: $eventData,
            metadata: $metadata,
        );
    }

    public static function eventType(): string
    {
        return 'signup.medication_selected';
    }

    public static function aggregateType(): string
    {
        return 'signup';
    }

    public function toStoredEventAttributes(): array
    {
        return [
            'aggregate_uuid' => $this->aggregateUuid,
            'aggregate_type' => self::aggregateType(),
            'event_type' => self::eventType(),
            'event_data' => json_encode([
                'signup_id' => $this->signupId,
                'medication_name' => $this->medicationName,
                ...$this->payload,
            ]),
            'metadata' => json_encode($this->metadata),
            'occurred_at' => now(),
        ];
    }
}

