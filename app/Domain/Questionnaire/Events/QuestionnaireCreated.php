<?php

namespace App\Domain\Questionnaire\Events;

use App\Domain\Events\DomainEvent;

class QuestionnaireCreated extends DomainEvent
{
    public function __construct(
        public readonly string $questionnaireId,
        public readonly string $title,
        public readonly string $description,
        public readonly array $questions,
        public readonly ?string $conditionId = null,
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
            title: $eventData['title'] ?? '',
            description: $eventData['description'] ?? '',
            questions: $eventData['questions'] ?? [],
            conditionId: $eventData['condition_id'] ?? null,
            payload: $eventData,
            metadata: $metadata,
        );
    }

    public static function eventType(): string
    {
        return 'questionnaire.created';
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
                'title' => $this->title,
                'description' => $this->description,
                'questions' => $this->questions,
                'condition_id' => $this->conditionId,
                ...$this->payload,
            ]),
            'metadata' => json_encode($this->metadata),
            'occurred_at' => now(),
        ];
    }
}

