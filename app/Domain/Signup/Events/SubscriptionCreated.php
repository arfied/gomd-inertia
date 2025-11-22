<?php

namespace App\Domain\Signup\Events;

use App\Domain\Events\DomainEvent;

class SubscriptionCreated extends DomainEvent
{
    public function __construct(
        public readonly string $signupId,
        public readonly string $subscriptionId,
        public readonly string $userId,
        public readonly string $planId,
        public readonly ?string $medicationId = null,
        public readonly ?string $conditionId = null,
        array $payload = [],
        array $metadata = [],
    ) {
        parent::__construct($signupId, $payload, $metadata);
    }

    public static function eventType(): string
    {
        return 'signup.subscription_created';
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
                'subscription_id' => $this->subscriptionId,
                'user_id' => $this->userId,
                'plan_id' => $this->planId,
                'medication_id' => $this->medicationId,
                'condition_id' => $this->conditionId,
                ...$this->payload,
            ]),
            'metadata' => json_encode($this->metadata),
            'occurred_at' => now(),
        ];
    }
}

