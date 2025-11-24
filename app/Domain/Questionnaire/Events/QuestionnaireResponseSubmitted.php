<?php

namespace App\Domain\Questionnaire\Events;

use App\Domain\Events\DomainEvent;

class QuestionnaireResponseSubmitted extends DomainEvent
{
    public function __construct(
        public readonly string $questionnaireId,
        public readonly string $patientId,
        public readonly array $responses,
        public readonly bool $isValid = true,
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
            patientId: $eventData['patient_id'] ?? '',
            responses: $eventData['responses'] ?? [],
            isValid: $eventData['is_valid'] ?? true,
            payload: $eventData,
            metadata: $metadata,
        );
    }

    public static function eventType(): string
    {
        return 'questionnaire.response_submitted';
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
                'patient_id' => $this->patientId,
                'responses' => $this->responses,
                'is_valid' => $this->isValid,
                ...$this->payload,
            ]),
            'metadata' => json_encode($this->metadata),
            'occurred_at' => now(),
        ];
    }
}

