<?php

namespace App\Application\Compliance\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for logging data access.
 */
class LogDataAccess implements Command
{
    public function __construct(
        public string $auditLogUuid,
        public string $patientId,
        public int $accessedBy,
        public string $accessType,
        public string $resource,
        public ?string $accessedAt = null,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public array $metadata = [],
    ) {
    }
}

