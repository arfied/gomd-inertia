<?php

namespace App\Domain\Signup\Events;

use App\Domain\Events\DomainEvent;

class SignupStarted extends DomainEvent
{
    public function __construct(
        public readonly string $signupId,
        public readonly string|int|null $userId,
        public readonly string $signupPath, // 'medication_first', 'condition_first', 'plan_first'
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
            userId: $eventData['user_id'] ?? null,
            signupPath: $eventData['signup_path'] ?? '',
            payload: $eventData,
            metadata: $metadata,
        );
    }

    public static function eventType(): string
    {
        return 'signup.started';
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
                'user_id' => $this->userId,
                'signup_path' => $this->signupPath,
                ...$this->payload,
            ]),
            'metadata' => json_encode($this->metadata),
            'occurred_at' => now(),
        ];
    }
}

