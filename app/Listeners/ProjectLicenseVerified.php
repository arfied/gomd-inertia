<?php

namespace App\Listeners;

use App\Domain\Compliance\Events\LicenseVerified;
use App\Models\LicenseReadModel;

class ProjectLicenseVerified
{
    public function handle(LicenseVerified $event): void
    {
        $payload = $event->payload;

        LicenseReadModel::updateOrCreate(
            ['license_uuid' => $event->aggregateUuid],
            [
                'provider_id' => $payload['provider_id'] ?? null,
                'license_number' => $payload['license_number'] ?? null,
                'license_type' => $payload['license_type'] ?? null,
                'verified_at' => $payload['verified_at'] ?? $event->occurredAt,
                'expires_at' => $payload['expires_at'] ?? null,
                'issuing_body' => $payload['issuing_body'] ?? null,
                'verification_url' => $payload['verification_url'] ?? null,
                'status' => $payload['status'] ?? 'verified',
                'created_at' => $event->occurredAt,
            ],
        );
    }
}

