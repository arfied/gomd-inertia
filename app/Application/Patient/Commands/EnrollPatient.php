<?php

namespace App\Application\Patient\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for enrolling a patient.
 *
 * This command intentionally contains only primitive data and no behavior.
 */
class EnrollPatient implements Command
{
    public function __construct(
        public string $patientUuid,
        public int $userId,
        public array $metadata = [],
    ) {
    }
}

