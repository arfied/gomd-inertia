<?php

namespace App\Application\Patient\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command to record a high-level visit summary / longitudinal medical history
 * snapshot for a patient.
 */
class RecordPatientVisitSummary implements Command
{
    /**
     * @param  array<int, string|array{name: string}>  $familyHistoryConditions
     */
    public function __construct(
        public string $patientUuid,
        public int $userId,
        public bool $pastInjuries,
        public ?string $pastInjuriesDetails,
        public bool $surgery,
        public ?string $surgeryDetails,
        public ?string $chronicConditionsDetails,
        public bool $chronicPain,
        public ?string $chronicPainDetails,
        public array $familyHistoryConditions = [],
        public array $metadata = [],
    ) {
    }
}

