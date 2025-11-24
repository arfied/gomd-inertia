<?php

namespace App\Domain\Questionnaire\Events;

use App\Domain\Events\DomainEvent;

class QuestionnaireValidationFailed extends DomainEvent
{
    public function __construct(
        public readonly string $questionnaireId,
        public readonly array $errors,
        public readonly array $failedResponses = [],
        array $payload = [],
        array $metadata = [],
    ) {
        parent::__construct($questionnaireId, $payload, $metadata);
    }

    /**
     * Reconstruct event from stored event data.
     * Used during event rehydration from the event store.
     */
    public static function fromStoredEventData(string $aggregateUuid, array $eventData, array $metadata = []): self
    {
        return new self(
            questionnaireId: $eventData['questionnaire_id'] ?? $aggregateUuid,
            errors: $eventData['errors'] ?? [],
            failedResponses: $eventData['failed_responses'] ?? [],
            payload: $eventData,
            metadata: $metadata,
        );
    }

    public static function eventType(): string
    {
        return 'questionnaire.validation_failed';
    }

    public static function aggregateType(): string
    {
        return 'questionnaire';
    }

    public function toStoredEventAttributes(): array
    {
        return [
            'aggregate_uuid' => $this->aggregateUuid,
            'aggregate_type' => self::aggregateType(),
            'event_type' => self::eventType(),
            'event_data' => json_encode([
                'questionnaire_id' => $this->questionnaireId,
                'errors' => $this->errors,
                'failed_responses' => $this->failedResponses,
                ...$this->payload,
            ]),
            'metadata' => json_encode($this->metadata),
            'occurred_at' => now(),
        ];
    }
}

