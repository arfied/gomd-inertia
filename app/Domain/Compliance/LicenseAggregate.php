<?php

namespace App\Domain\Compliance;

use App\Domain\Compliance\Events\LicenseVerified;
use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for the License bounded context.
 *
 * Represents healthcare provider licenses and their verification status.
 * Tracks license verification, expiration, and compliance status.
 */
class LicenseAggregate extends AggregateRoot
{
    public string $uuid;
    public string $providerId;
    public string $licenseNumber;
    public string $licenseType; // MD, DO, NP, PA, RN, etc.
    public string $verifiedAt;
    public ?string $expiresAt = null;
    public string $issuingBody;
    public string $status = 'verified'; // verified, expired, suspended, revoked
    public ?string $verificationUrl = null;

    /**
     * Create a new license aggregate and record a LicenseVerified event.
     *
     * @param  string  $uuid
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public static function create(string $uuid, array $payload = [], array $metadata = []): self
    {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new LicenseVerified($uuid, $payload, $metadata));

        return $aggregate;
    }

    protected function apply(DomainEvent $event): void
    {
        if ($event instanceof LicenseVerified) {
            $this->uuid = $event->aggregateUuid;
            $this->providerId = $event->payload['provider_id'] ?? '';
            $this->licenseNumber = $event->payload['license_number'] ?? '';
            $this->licenseType = $event->payload['license_type'] ?? '';
            $this->verifiedAt = $event->payload['verified_at'] ?? now()->toIso8601String();
            $this->expiresAt = $event->payload['expires_at'] ?? null;
            $this->issuingBody = $event->payload['issuing_body'] ?? '';
            $this->verificationUrl = $event->payload['verification_url'] ?? null;
            $this->status = 'verified';
        }
    }
}

