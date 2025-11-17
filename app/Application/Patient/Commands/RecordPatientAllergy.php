<?php

namespace App\Application\Patient\Commands;

use App\Domain\Shared\Commands\Command;

class RecordPatientAllergy implements Command
{
    public function __construct(
        public string $patientUuid,
        public int $userId,
        public string $allergen,
        public ?string $reaction = null,
        public ?string $severity = null,
        public ?string $notes = null,
        public array $metadata = [],
    ) {
    }
}

