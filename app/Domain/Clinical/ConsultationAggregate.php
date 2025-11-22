<?php

namespace App\Domain\Clinical;

use App\Domain\Clinical\Events\ConsultationScheduled;
use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for the Consultation bounded context.
 *
 * Represents a scheduled consultation between a patient and healthcare provider.
 * Tracks scheduling, status, and consultation details.
 */
class ConsultationAggregate extends AggregateRoot
{
    public string $uuid;
    public string $patientId;
    public string $doctorId;
    public string $scheduledAt;
    public int $duration; // in minutes
    public string $status = 'scheduled'; // scheduled, in_progress, completed, cancelled
    public ?string $notes = null;
    public ?string $completedAt = null;

    /**
     * Create a new consultation aggregate and record a ConsultationScheduled event.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function create(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new ConsultationScheduled($uuid, $payload, $metadata));

        return $aggregate;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof ConsultationScheduled) {
            $this->uuid = $event->aggregateUuid;
            $this->patientId = $event->payload['patient_id'] ?? '';
            $this->doctorId = $event->payload['doctor_id'] ?? '';
            $this->scheduledAt = $event->payload['scheduled_at'] ?? '';
            $this->duration = $event->payload['duration'] ?? 30;
            $this->status = $event->payload['status'] ?? 'scheduled';
            $this->notes = $event->payload['notes'] ?? null;
        }
    }
}

