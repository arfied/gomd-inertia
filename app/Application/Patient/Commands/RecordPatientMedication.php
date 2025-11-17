<?php

namespace App\Application\Patient\Commands;

use App\Domain\Shared\Commands\Command;

class RecordPatientMedication implements Command
{
    public function __construct(
        public string $patientUuid,
        public int $userId,
        public int $medicationId,
        public string $dosage,
        public string $frequency,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?string $notes = null,
        public array $metadata = [],
    ) {
    }
}

