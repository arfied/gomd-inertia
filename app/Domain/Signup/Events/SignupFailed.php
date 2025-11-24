<?php

namespace App\Domain\Signup\Events;

use App\Domain\Events\DomainEvent;

class SignupFailed extends DomainEvent
{
    public function __construct(
        public readonly string $signupId,
        public readonly string $reason, // 'validation_error', 'payment_failed', 'system_error'
        public readonly string $message,
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
            reason: $eventData['reason'] ?? 'system_error',
            message: $eventData['message'] ?? '',
            payload: $eventData,
            metadata: $metadata,
        );
    }

    public static function eventType(): string
    {
        return 'signup.failed';
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
                'reason' => $this->reason,
                'message' => $this->message,
                ...$this->payload,
            ]),
            'metadata' => json_encode($this->metadata),
            'occurred_at' => now(),
        ];
    }
}

