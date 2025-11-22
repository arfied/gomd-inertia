<?php

namespace App\Domain\Compliance\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a healthcare provider's license is verified.
 *
 * Payload includes: provider_id, license_number, license_type, verified_at, expires_at, issuing_body.
 */
class LicenseVerified extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'license';
    }

    public static function eventType(): string
    {
        return 'license.verified';
    }
}

