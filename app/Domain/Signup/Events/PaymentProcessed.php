<?php

namespace App\Domain\Signup\Events;

use App\Domain\Events\DomainEvent;

class PaymentProcessed extends DomainEvent
{
    public function __construct(
        public readonly string $signupId,
        public readonly string $paymentId,
        public readonly float $amount,
        public readonly string $status, // 'success', 'pending', 'failed'
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
            paymentId: $eventData['payment_id'] ?? '',
            amount: (float) ($eventData['amount'] ?? 0),
            status: $eventData['status'] ?? 'pending',
            payload: $eventData,
            metadata: $metadata,
        );
    }

    public static function eventType(): string
    {
        return 'signup.payment_processed';
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
                'payment_id' => $this->paymentId,
                'amount' => $this->amount,
                'status' => $this->status,
                ...$this->payload,
            ]),
            'metadata' => json_encode($this->metadata),
            'occurred_at' => now(),
        ];
    }
}

