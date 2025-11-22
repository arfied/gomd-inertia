<?php

namespace App\Application\Compliance\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for verifying a provider license.
 */
class VerifyProviderLicense implements Command
{
    public function __construct(
        public string $licenseUuid,
        public int $providerId,
        public string $licenseNumber,
        public string $licenseType,
        public ?string $verifiedAt = null,
        public ?string $expiresAt = null,
        public ?string $issuingBody = null,
        public ?string $verificationUrl = null,
        public array $metadata = [],
    ) {
    }
}

