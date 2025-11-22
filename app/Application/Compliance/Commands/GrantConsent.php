<?php

namespace App\Application\Compliance\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for granting patient consent.
 */
class GrantConsent implements Command
{
    public function __construct(
        public string $consentUuid,
        public string $patientId,
        public string $consentType,
        public int $grantedBy,
        public ?string $grantedAt = null,
        public ?string $expiresAt = null,
        public ?string $termsVersion = null,
        public array $metadata = [],
    ) {
    }
}

