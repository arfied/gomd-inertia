<?php

namespace App\Domain\Clinical;

use App\Domain\Clinical\Events\ClinicalNoteRecorded;
use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for the Clinical Note bounded context.
 *
 * Represents a clinical note recorded by a healthcare provider during or after
 * a patient consultation or visit.
 */
class ClinicalNoteAggregate extends AggregateRoot
{
    public string $uuid;
    public string $patientId;
    public string $doctorId;
    public string $content;
    public string $noteType; // progress, assessment, plan, soap, etc.
    public string $recordedAt;
    public array $attachments = [];

    /**
     * Create a new clinical note aggregate and record a ClinicalNoteRecorded event.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function create(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new ClinicalNoteRecorded($uuid, $payload, $metadata));

        return $aggregate;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof ClinicalNoteRecorded) {
            $this->uuid = $event->aggregateUuid;
            $this->patientId = $event->payload['patient_id'] ?? '';
            $this->doctorId = $event->payload['doctor_id'] ?? '';
            $this->content = $event->payload['content'] ?? '';
            $this->noteType = $event->payload['note_type'] ?? 'progress';
            $this->recordedAt = $event->payload['recorded_at'] ?? now()->toIso8601String();
            $this->attachments = $event->payload['attachments'] ?? [];
        }
    }
}

