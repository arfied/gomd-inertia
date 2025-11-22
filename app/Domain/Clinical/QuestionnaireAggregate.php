<?php

namespace App\Domain\Clinical;

use App\Domain\Clinical\Events\QuestionnaireCreated;
use App\Domain\Clinical\Events\ResponseSubmitted;
use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for the Questionnaire bounded context.
 *
 * Represents a clinical questionnaire that can be created, distributed to patients,
 * and responses collected and analyzed.
 */
class QuestionnaireAggregate extends AggregateRoot
{
    public string $uuid;
    public string $title;
    public string $description;
    public array $questions = [];
    public string $status = 'draft'; // draft, active, archived
    public string $createdBy;
    public ?string $patientId = null;
    public array $responses = [];

    /**
     * Create a new questionnaire aggregate and record a QuestionnaireCreated event.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function create(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new QuestionnaireCreated($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Submit responses to the questionnaire.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function submitResponse(array $payload = [], array $metadata = []): void
    {
        $this->recordThat(new ResponseSubmitted($this->uuid, $payload, $metadata));
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof QuestionnaireCreated) {
            $this->uuid = $event->aggregateUuid;
            $this->title = $event->payload['title'] ?? '';
            $this->description = $event->payload['description'] ?? '';
            $this->questions = $event->payload['questions'] ?? [];
            $this->status = $event->payload['status'] ?? 'draft';
            $this->createdBy = $event->payload['created_by'] ?? '';
            $this->patientId = $event->payload['patient_id'] ?? null;
        } elseif ($event instanceof ResponseSubmitted) {
            $this->responses[] = $event->payload;
        }
    }
}

