<?php

namespace App\Application\Clinical\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for scheduling a consultation.
 */
class ScheduleConsultation implements Command
{
    public function __construct(
        public string $consultationUuid,
        public string $patientId,
        public int $doctorId,
        public string $scheduledAt,
        public ?string $reason = null,
        public ?string $notes = null,
        public array $metadata = [],
    ) {
    }
}

