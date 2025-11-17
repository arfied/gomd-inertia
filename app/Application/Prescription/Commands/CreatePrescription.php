<?php

namespace App\Application\Prescription\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for creating a prescription.
 */
class CreatePrescription implements Command
{
    public function __construct(
        public string $prescriptionUuid,
        public int $patientId,
        public int $doctorId,
        public ?string $notes = null,
        public bool $isNonStandard = false,
        public array $metadata = [],
    ) {
    }
}

