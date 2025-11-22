<?php

namespace App\Domain\Compliance;

use App\Domain\Compliance\Events\ConsentGranted;
use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for the Consent bounded context.
 *
 * Represents patient consent for data usage, treatment, and privacy policies.
 * Tracks consent grants, revocations, and expiration.
 */
class ConsentAggregate extends AggregateRoot
{
    public string $uuid;
    public string $patientId;
    public string $consentType; // treatment, privacy, data_sharing, etc.
    public string $grantedBy;
    public string $grantedAt;
    public ?string $expiresAt = null;
    public string $termsVersion;
    public string $status = 'active'; // active, revoked, expired

    /**
     * Create a new consent aggregate and record a ConsentGranted event.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function create(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new ConsentGranted($uuid, $payload, $metadata));

        return $aggregate;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof ConsentGranted) {
            $this->uuid = $event->aggregateUuid;
            $this->patientId = $event->payload['patient_id'] ?? '';
            $this->consentType = $event->payload['consent_type'] ?? '';
            $this->grantedBy = $event->payload['granted_by'] ?? '';
            $this->grantedAt = $event->payload['granted_at'] ?? now()->toIso8601String();
            $this->expiresAt = $event->payload['expires_at'] ?? null;
            $this->termsVersion = $event->payload['terms_version'] ?? '1.0';
            $this->status = 'active';
        }
    }
}

