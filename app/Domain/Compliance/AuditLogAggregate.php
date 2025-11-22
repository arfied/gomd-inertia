<?php

namespace App\Domain\Compliance;

use App\Domain\Compliance\Events\AccessLogged;
use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for the Audit Log bounded context.
 *
 * Represents an audit trail of all access to patient data for compliance
 * and regulatory requirements (HIPAA, GDPR, etc.).
 */
class AuditLogAggregate extends AggregateRoot
{
    public string $uuid;
    public string $patientId;
    public string $accessedBy;
    public string $accessType; // read, write, delete, export, etc.
    public string $resource; // patient_record, medical_history, etc.
    public string $accessedAt;
    public ?string $ipAddress = null;
    public ?string $userAgent = null;
    public string $status = 'logged';

    /**
     * Create a new audit log aggregate and record an AccessLogged event.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function create(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new AccessLogged($uuid, $payload, $metadata));

        return $aggregate;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof AccessLogged) {
            $this->uuid = $event->aggregateUuid;
            $this->patientId = $event->payload['patient_id'] ?? '';
            $this->accessedBy = $event->payload['accessed_by'] ?? '';
            $this->accessType = $event->payload['access_type'] ?? 'read';
            $this->resource = $event->payload['resource'] ?? '';
            $this->accessedAt = $event->payload['accessed_at'] ?? now()->toIso8601String();
            $this->ipAddress = $event->payload['ip_address'] ?? null;
            $this->userAgent = $event->payload['user_agent'] ?? null;
        }
    }
}

