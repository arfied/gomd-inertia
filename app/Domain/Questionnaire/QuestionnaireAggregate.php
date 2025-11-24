<?php

namespace App\Domain\Questionnaire;

use App\Domain\Events\DomainEvent;
use App\Domain\Questionnaire\Events\QuestionnaireCreated;
use App\Domain\Questionnaire\Events\QuestionnaireResponseSubmitted;
use App\Domain\Questionnaire\Events\QuestionnaireValidationFailed;
use App\Domain\Shared\AggregateRoot;
use App\Models\StoredEvent;

class QuestionnaireAggregate extends AggregateRoot
{
    public string $questionnaireId;
    public string $title;
    public string $description;
    public array $questions = [];
    public ?string $conditionId = null;
    public array $responses = [];
    public bool $isSubmitted = false;
    public array $validationErrors = [];

    public static function create(
        string $questionnaireId,
        string $title,
        string $description,
        array $questions,
        ?string $conditionId = null,
        array $metadata = [],
    ): self {
        $aggregate = new self();
        $aggregate->questionnaireId = $questionnaireId;

        $aggregate->recordThat(new QuestionnaireCreated(
            $questionnaireId,
            $title,
            $description,
            $questions,
            $conditionId,
            metadata: $metadata,
        ));

        return $aggregate;
    }

    /**
     * Reconstruct aggregate from event history in the event store.
     */
    public static function fromEventStream(string $questionnaireId): self
    {
        $registry = app(\App\Services\ProjectionRegistry::class);

        $events = StoredEvent::where('aggregate_uuid', $questionnaireId)
            ->where('aggregate_type', self::aggregateType())
            ->orderBy('id')
            ->get()
            ->map(function ($stored) use ($registry) {
                $class = $registry->eventClassFor($stored->event_type);

                if ($class === null || ! is_subclass_of($class, \App\Domain\Events\DomainEvent::class)) {
                    return null;
                }

                $eventData = $stored->event_data;
                if (is_string($eventData)) {
                    $eventData = json_decode($eventData, true) ?? [];
                }

                $metadata = $stored->metadata;
                if (is_string($metadata)) {
                    $metadata = json_decode($metadata, true) ?? [];
                }

                if (method_exists($class, 'fromStoredEventData')) {
                    $event = $class::fromStoredEventData(
                        $stored->aggregate_uuid,
                        $eventData,
                        $metadata,
                    );
                } else {
                    $event = new $class(
                        $stored->aggregate_uuid,
                        $eventData,
                        $metadata,
                    );
                }

                if ($stored->occurred_at instanceof \DateTimeInterface) {
                    $event->occurredAt = \DateTimeImmutable::createFromInterface($stored->occurred_at);
                }

                return $event;
            })
            ->filter(fn ($event) => $event !== null)
            ->all();

        return self::reconstituteFromHistory($events);
    }

    public static function aggregateType(): string
    {
        return 'questionnaire';
    }

    public function submitResponse(string $patientId, array $responses, array $metadata = []): void
    {
        $this->recordThat(new QuestionnaireResponseSubmitted(
            $this->questionnaireId,
            $patientId,
            $responses,
            true,
            metadata: $metadata,
        ));
    }

    public function failValidation(array $errors, array $failedResponses = [], array $metadata = []): void
    {
        $this->recordThat(new QuestionnaireValidationFailed(
            $this->questionnaireId,
            $errors,
            $failedResponses,
            metadata: $metadata,
        ));
    }

    protected function apply(DomainEvent $event): void
    {
        match ($event::class) {
            QuestionnaireCreated::class => $this->applyQuestionnaireCreated($event),
            QuestionnaireResponseSubmitted::class => $this->applyResponseSubmitted($event),
            QuestionnaireValidationFailed::class => $this->applyValidationFailed($event),
            default => null,
        };
    }

    private function applyQuestionnaireCreated(QuestionnaireCreated $event): void
    {
        $this->questionnaireId = $event->questionnaireId;
        $this->title = $event->title;
        $this->description = $event->description;
        $this->questions = $event->questions;
        $this->conditionId = $event->conditionId;
    }

    private function applyResponseSubmitted(QuestionnaireResponseSubmitted $event): void
    {
        $this->responses = $event->responses;
        $this->isSubmitted = $event->isValid;
    }

    private function applyValidationFailed(QuestionnaireValidationFailed $event): void
    {
        $this->validationErrors = $event->errors;
    }
}

